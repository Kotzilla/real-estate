<?php

namespace App\Console\Commands;

use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Clean Photos Command
 *
 * Tidies the photo files on the public disk (img/ and thumb/{sm,md,lg}/):
 * - `php artisan photos:clean` deletes files no photo row points at, deletes photo rows whose original is gone, and
 *   makes any missing thumbnails again (safe: a photo in use is never touched).
 * - `php artisan photos:clean --all` deletes every photo, rows and files (e.g. after uploading test images); it asks
 *   first unless `--force`, and never runs in production.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Console\Commands
 */
class CleanPhotos extends Command
{
    protected $signature = 'photos:clean
        {--all : Delete every photo (rows and files), not only leftovers}
        {--force : Do not ask before --all}';

    protected $description = 'Delete leftover photo files and rows (or every photo with --all), and remake missing thumbnails';

    /**
     * Run the clean-up
     *
     * @return int
     */
    public function handle(): int
    {
        return $this->option('all') ? $this->deleteAll() : $this->sweep();
    }

    /**
     * Delete every photo row and every file under img/ and thumb/
     *
     * @return int
     */
    private function deleteAll(): int
    {
        if (app()->isProduction()) {
            $this->error('photos:clean --all is not allowed in production.');

            return self::FAILURE;
        }
        $count = Photo::count();
        if (! $this->option('force') && ! $this->confirm("Delete all {$count} photos and their files?")) {
            return self::SUCCESS;
        }

        Photo::query()->delete(); // one query: the files go below, not one by one through the model event
        $files = $this->files();
        Storage::disk('public')->delete($files);
        $this->info("Deleted {$count} photos and ".count($files).' files.');

        return self::SUCCESS;
    }

    /**
     * Delete leftover files and rows, then remake missing thumbnails
     *
     * @return int
     */
    private function sweep(): int
    {
        $disk = Storage::disk('public');

        $missing = Photo::all()->reject(fn (Photo $photo) => $disk->exists($photo->path));
        $missing->each->delete();

        $used = Photo::all()->flatMap->files()->flip();
        $orphans = array_values(array_filter($this->files(), fn ($file) => ! isset($used[$file])));
        $disk->delete($orphans);

        $remade = 0;
        foreach (Photo::all() as $photo) {
            if (collect($photo->files())->contains(fn ($file) => ! $disk->exists($file))) {
                $photo->makeThumbs();
                $remade++;
            }
        }

        $this->info(count($orphans).' leftover files deleted, '.$missing->count().' photos without a file removed, '.$remade.' photos given their thumbnails again.');

        return self::SUCCESS;
    }

    /**
     * Every image file under img/ and thumb/ (the .gitignore that keeps each folder in git is left alone)
     *
     * @return array<int, string>
     */
    private function files(): array
    {
        $disk = Storage::disk('public');

        return array_values(array_filter(
            [...$disk->allFiles('img'), ...$disk->allFiles('thumb')],
            fn ($file) => basename($file) !== '.gitignore',
        ));
    }
}
