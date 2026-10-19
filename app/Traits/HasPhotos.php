<?php

namespace App\Traits;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\UploadedFile;

/**
 * Has Photos
 *
 * The photo gallery of a model (properties today): ordered by position, with one
 * cover — the first photo becomes it, and removing the cover hands it to the next one.
 *
 * @package App\Traits
 */
trait HasPhotos
{
    /**
     * The gallery, in order
     *
     * @return MorphMany<Photo, $this>
     */
    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable')->orderBy('position')->orderBy('id');
    }

    /**
     * The photo shown in lists
     *
     * @return MorphOne<Photo, $this>
     */
    public function coverPhoto(): MorphOne
    {
        return $this->morphOne(Photo::class, 'photoable')->where('is_cover', true);
    }

    /**
     * Store an uploaded image on the public disk (img/, with its thumb/ copies) and add it to the end of the gallery
     *
     * @param  UploadedFile  $file
     * @param  int|null  $userId
     * @return Photo
     */
    public function addPhoto(UploadedFile $file, ?int $userId = null): Photo
    {
        $size = getimagesize($file->getRealPath());
        [$width, $height] = is_array($size) ? [$size[0], $size[1]] : [null, null];

        $photo = $this->photos()->create([
            'user_id' => $userId,
            'path' => $file->store('img', 'public'), // a random name, so one flat folder holds every model's photos
            'name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'position' => (int) $this->photos()->max('position') + 1,
            'is_cover' => ! $this->photos()->where('is_cover', true)->exists(),
        ]);
        $photo->makeThumbs();

        return $photo;
    }

    /**
     * Delete some of the gallery's photos (ids from other models are ignored)
     *
     * @param  array<int, int|string>  $photoIds
     * @return void
     */
    public function removePhotos(array $photoIds): void
    {
        $this->photos()->whereKey($photoIds)->get()->each->delete();
        $this->ensureCover();
    }

    /**
     * Make one of the gallery's photos the cover
     *
     * @param  int  $photoId
     * @return void
     */
    public function makeCover(int $photoId): void
    {
        if ($this->photos()->whereKey($photoId)->exists()) {
            $this->photos()->update(['is_cover' => false]);
            $this->photos()->whereKey($photoId)->update(['is_cover' => true]);
        }
    }

    /**
     * Give the gallery a cover again when it has photos but none is the cover
     *
     * @return void
     */
    protected function ensureCover(): void
    {
        if (! $this->photos()->where('is_cover', true)->exists()) {
            $this->photos()->first()?->update(['is_cover' => true]);
        }
    }
}
