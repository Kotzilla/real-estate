<?php

namespace Tests\Feature;

use App\Models\Photo;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    use RefreshDatabase;

    private Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->property = Property::factory()->create();
    }

    /**
     * Width × height of a file on the fake public disk
     */
    private function dimensions(string $path): array
    {
        return array_slice(getimagesize(Storage::disk('public')->path($path)), 0, 2);
    }

    public function test_an_upload_keeps_the_original_in_img_and_makes_sm_md_lg_copies(): void
    {
        $photo = $this->property->addPhoto(UploadedFile::fake()->image('look.jpg', 2000, 2500));

        $this->assertStringStartsWith('img/', $photo->path);
        $this->assertSame([2000, 2500], $this->dimensions($photo->path));
        $this->assertSame([480, 600], $this->dimensions($photo->thumbPath('sm')));
        $this->assertSame([960, 1200], $this->dimensions($photo->thumbPath('md')));
        $this->assertSame([1600, 2000], $this->dimensions($photo->thumbPath('lg')));
        $this->assertStringEndsWith('/thumb/md/'.basename($photo->path), $photo->src());
        $this->assertStringEndsWith('/'.$photo->path, $photo->src('org'));
    }

    public function test_a_small_image_is_copied_never_enlarged(): void
    {
        $photo = $this->property->addPhoto(UploadedFile::fake()->image('small.png', 300, 400));

        foreach (array_keys(Photo::SIZES) as $size) {
            $this->assertSame([300, 400], $this->dimensions($photo->thumbPath($size)));
        }
    }

    public function test_deleting_a_photo_deletes_the_original_and_its_copies(): void
    {
        $photo = $this->property->addPhoto(UploadedFile::fake()->image('look.jpg', 1000, 1000));
        $files = $photo->files();

        $this->property->removePhotos([$photo->id]);

        foreach ($files as $file) {
            Storage::disk('public')->assertMissing($file);
        }
    }

    public function test_clean_sweeps_leftover_files_and_rows_and_remakes_thumbnails(): void
    {
        $disk = Storage::disk('public');
        $kept = $this->property->addPhoto(UploadedFile::fake()->image('kept.jpg', 1000, 1000));
        $gone = $this->property->addPhoto(UploadedFile::fake()->image('gone.jpg', 1000, 1000));
        $disk->delete($gone->path); // its original vanished
        $disk->delete($kept->thumbPath('md')); // a thumbnail went missing
        $disk->put('img/orphan.jpg', 'x');
        $disk->put('thumb/sm/orphan.jpg', 'x');
        $disk->put('img/.gitignore', "*\n!.gitignore\n");

        $this->artisan('photos:clean')->assertSuccessful();

        $this->assertModelExists($kept);
        $this->assertModelMissing($gone);
        $disk->assertExists($kept->thumbPath('md'));
        $disk->assertMissing(['img/orphan.jpg', 'thumb/sm/orphan.jpg', $gone->thumbPath('sm')]);
        $disk->assertExists('img/.gitignore');
    }

    public function test_clean_all_deletes_every_photo_but_keeps_the_folders(): void
    {
        $disk = Storage::disk('public');
        $photo = $this->property->addPhoto(UploadedFile::fake()->image('test.jpg', 1000, 1000));
        $disk->put('thumb/lg/.gitignore', "*\n!.gitignore\n");

        $this->artisan('photos:clean --all')->expectsConfirmation('Delete all 1 photos and their files?', 'no')->assertSuccessful();
        $this->assertModelExists($photo);

        $this->artisan('photos:clean --all --force')->assertSuccessful();
        $this->assertSame(0, Photo::count());
        $this->assertSame(['thumb/lg/.gitignore'], [...$disk->allFiles('img'), ...$disk->allFiles('thumb')]);
    }
}
