<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Photo
 *
 * A public image of a property (photoable, a morph-map alias). On the "public" disk the original is
 * `img/<name>` and its resized copies `thumb/{sm,md,lg}/<name>` (same layout as the store project); all of them go
 * with the row. Copies are made with GD on upload (makeThumbs); `php artisan photos:clean` sweeps leftovers.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ErrorControlOperator)
 * @package App\Models
 */
class Photo extends Model
{
    /** Thumbnail sizes: name => longest width in pixels (height up to 1.5× for portrait shots) */
    public const SIZES = ['sm' => 480, 'md' => 960, 'lg' => 1600];

    protected $fillable = ['user_id', 'path', 'name', 'mime', 'size', 'width', 'height', 'alt', 'position', 'is_cover'];

    /**
     * The attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
        ];
    }

    /**
     * Delete the file when the row goes
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::deleted(fn (Photo $photo) => Storage::disk('public')->delete($photo->files()));
    }

    /**
     * What the photo is of
     *
     * @return MorphTo<Model, $this>
     */
    public function photoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Who uploaded it
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The original and every thumbnail, as paths on the public disk
     *
     * @return array<int, string>
     */
    public function files(): array
    {
        return [$this->path, ...array_map(fn ($size) => $this->thumbPath($size), array_keys(self::SIZES))];
    }

    /**
     * Where one thumbnail size lives on the public disk
     *
     * @param  string  $size
     * @return string
     */
    public function thumbPath(string $size): string
    {
        return 'thumb/'.$size.'/'.basename($this->path);
    }

    /**
     * The URL of a thumbnail (sm 480, md 960, lg 1600 wide) or of the original ('org')
     *
     * @param  string  $size
     * @return string
     */
    public function src(string $size = 'md'): string
    {
        return Storage::disk('public')->url($size === 'org' ? $this->path : $this->thumbPath($size));
    }

    /**
     * Make the sm / md / lg copies of the original with GD. A copy is never larger than the original: a smaller
     * image, or one GD can't read (e.g. WebP without GD WebP support), is copied as it is.
     *
     * @return void
     */
    public function makeThumbs(): void
    {
        $disk = Storage::disk('public');
        $source = $disk->path($this->path);
        $image = self::read($source);

        foreach (self::SIZES as $size => $width) {
            $target = $disk->path($this->thumbPath($size));
            File::ensureDirectoryExists(dirname($target));
            $scale = $image ? min(1, $width / imagesx($image), $width * 1.5 / imagesy($image)) : 1;
            if (! $image || $scale >= 1) {
                File::copy($source, $target);

                continue;
            }
            $copy = imagecreatetruecolor(max(1, (int) round(imagesx($image) * $scale)), max(1, (int) round(imagesy($image) * $scale)));
            imagealphablending($copy, false); // keep PNG / WebP transparency
            imagesavealpha($copy, true);
            imagecopyresampled($copy, $image, 0, 0, 0, 0, imagesx($copy), imagesy($copy), imagesx($image), imagesy($image));
            self::write($copy, $target);
        }
    }

    /**
     * Read an image with GD, turned upright by its EXIF orientation (phone photos); null when GD can't read it
     *
     * @param  string  $file
     * @return \GdImage|null
     */
    private static function read(string $file): ?\GdImage
    {
        $type = @getimagesize($file)[2] ?? null;
        $image = match (true) {
            $type === IMAGETYPE_JPEG => @imagecreatefromjpeg($file),
            $type === IMAGETYPE_PNG => @imagecreatefrompng($file),
            $type === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp') => @imagecreatefromwebp($file),
            default => false,
        };
        if (! $image) {
            return null;
        }

        // ponytail: no exif extension (the Docker image today) = phone photos are not turned upright in the copies
        $orientation = $type === IMAGETYPE_JPEG && function_exists('exif_read_data') ? (@exif_read_data($file)['Orientation'] ?? 1) : 1;
        $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;

        return $angle ? imagerotate($image, $angle, 0) : $image;
    }

    /**
     * Save a GD image in the format its file name asks for
     *
     * @param  \GdImage  $image
     * @param  string  $file
     * @return void
     */
    private static function write(\GdImage $image, string $file): void
    {
        match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'png' => imagepng($image, $file, 6),
            'webp' => imagewebp($image, $file, 82),
            default => imagejpeg($image, $file, 82),
        };
    }

    /**
     * The public URL of the original image
     *
     * @return Attribute<string, never>
     */
    protected function url(): Attribute
    {
        return Attribute::get(fn () => Storage::disk('public')->url($this->path));
    }
}
