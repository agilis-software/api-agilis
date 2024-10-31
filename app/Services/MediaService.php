<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    public static function saveMedia(UploadedFile $file, string $folder, string $name, string $disk = 'public')
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = $name.'.'.$extension;

        return $file->storeAs($folder, $fileName, $disk);
    }

    public static function deleteMedia(string $filePath, string $disk = 'public')
    {
        if (Storage::disk($disk)->exists($filePath)) {
            Storage::disk($disk)->delete($filePath);
        }
    }
}
