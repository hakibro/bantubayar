<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Exception;

trait ImageCompressor
{
    /**
     * Kompres dan simpan satu gambar
     */
    public function compressImage(UploadedFile $file, int $maxSize = 524288, int $maxWidth = 1200): ?string
    {
        try {
            // Validasi file
            if (!$file->isValid()) {
                \Log::warning('File tidak valid', ['error' => $file->getErrorMessage()]);
                return null;
            }

            // Cek mime type
            $mime = $file->getMimeType();
            if (!str_starts_with($mime, 'image/')) {
                \Log::warning('File bukan gambar', ['mime' => $mime]);
                return null;
            }

            // Baca gambar
            $image = Image::read($file->getRealPath());

            // Resize jika lebar > maxWidth
            if ($image->width() > $maxWidth) {
                $image->scale(width: $maxWidth);
            }

            // Encode ke JPEG dengan kualitas awal 85
            $quality = 85;
            $encoded = $image->toJpeg($quality);

            // Turunkan kualitas hingga ≤ maxSize atau kualitas minimum 20
            while ($encoded->size() > $maxSize && $quality > 20) {
                $quality -= 5;
                $encoded = $image->toJpeg($quality);
            }

            // Nama file unik
            $filename = uniqid() . '.jpg';
            $path = 'home-visit-fotos/' . $filename;

            // Simpan
            Storage::disk('public')->put($path, $encoded);

            \Log::info('Gambar berhasil dikompres', ['path' => $path]);
            return $path;

        } catch (Exception $e) {
            \Log::error('Gagal mengompres: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName()
            ]);

            // FALLBACK: simpan file asli tanpa kompresi
            try {
                $filename = uniqid() . '_original.' . $file->getClientOriginalExtension();
                $path = 'home-visit-fotos/' . $filename;
                Storage::disk('public')->putFileAs('home-visit-fotos', $file, $filename);
                \Log::info('Fallback: file asli disimpan', ['path' => $path]);
                return $path;
            } catch (Exception $fallback) {
                \Log::error('Fallback gagal: ' . $fallback->getMessage());
                return null;
            }
        }
    }

    /**
     * Kompres banyak gambar
     */
    public function compressMultipleImages(array $files): array
    {
        $savedPaths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $path = $this->compressImage($file);
                if ($path) {
                    $savedPaths[] = $path;
                }
            }
        }
        return $savedPaths;
    }
}