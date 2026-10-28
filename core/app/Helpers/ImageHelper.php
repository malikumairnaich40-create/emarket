<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageHelper
{
    /**
     * Whitelist of allowed image extensions.
     */
    protected static array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'];

    /**
     * Validate and sanitize file extension.
     */
    protected static function getSafeExtension($file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, self::$allowedExtensions, true)) {
            // Check guessExtension based on mime type
            $guessed = strtolower($file->guessExtension() ?? '');
            if (in_array($guessed, self::$allowedExtensions, true)) {
                $ext = $guessed;
            } else {
                throw new \InvalidArgumentException('Invalid file type uploaded. Only image files are permitted.');
            }
        }
        return $ext;
    }

    public static function handleUploadedImage($file, $path, $delete = null)
    {
        if ($file) {
            if ($delete) {
                Storage::delete($path . '/' . $delete);
            }

            $ext = self::getSafeExtension($file);
            $name = 'OM_' . time() . '_' . Str::random(12) . '.' . $ext;
            Storage::putFileAs($path, $file, $name);

            return $name;
        }
    }

    public static function uploadSummernoteImage($file, $path)
    {
        if (!file_exists($path)) {
            @mkdir($path, 0755, true);
        }

        if ($file) {
            $ext = self::getSafeExtension($file);
            $name = 'OM_' . time() . '_' . Str::random(12) . '.' . $ext;
            Storage::putFileAs($path, $file, $name);

            return $name;
        }
    }

    public static function ItemhandleUploadedImage($file, $path, $delete = null)
    {
        if ($file) {
            if ($delete) {
                Storage::delete($path . '/' . $delete);
            }

            $ext = self::getSafeExtension($file);
            $photoName = 'OM_' . time() . '_' . Str::random(12) . '.' . $ext;
            $thumbnailName = 'OM_thumb_' . time() . '_' . Str::random(12) . '.' . $ext;

            Storage::putFileAs($path, $file, $photoName);

            try {
                $image = \Image::make($file)->resize(230, 230);
                $thumbnailPath = $path . '/' . $thumbnailName;
                Storage::put($thumbnailPath, (string) $image->encode());
            } catch (\Throwable $e) {
                $thumbnailName = $photoName;
            }

            return [$photoName, $thumbnailName];
        }
    }

    public static function handleUpdatedUploadedImage($file, $path, $data, $delete_path, $field)
    {
        $ext = self::getSafeExtension($file);
        $name = 'OM_' . time() . '_' . Str::random(12) . '.' . $ext;

        Storage::putFileAs($path, $file, $name);

        if (!empty($data[$field])) {
            Storage::delete($delete_path . '/' . $data[$field]);
        }

        return $name;
    }

    public static function ItemhandleUpdatedUploadedImage($file, $path, $data, $delete_path, $field)
    {
        $ext = self::getSafeExtension($file);
        $photoName = 'OM_' . time() . '_' . Str::random(12) . '.' . $ext;
        $thumbnailName = 'OM_thumb_' . time() . '_' . Str::random(12) . '.' . $ext;

        try {
            $image = \Image::make($file)->resize(230, 230);
            $thumbnailPath = $path . '/' . $thumbnailName;
            Storage::put($thumbnailPath, (string) $image->encode());
        } catch (\Throwable $e) {
            $thumbnailName = $photoName;
        }

        Storage::putFileAs($path, $file, $photoName);

        if (!empty($data['thumbnail'])) {
            Storage::delete($delete_path . '/' . $data['thumbnail']);
        }

        if (!empty($data[$field])) {
            Storage::delete($delete_path . '/' . $data[$field]);
        }

        return [$photoName, $thumbnailName];
    }

    public static function handleDeletedImage($data, $field, $delete_path)
    {
        if (!empty($data[$field])) {
            Storage::delete($delete_path . '/' . $data[$field]);
        }
    }
}
