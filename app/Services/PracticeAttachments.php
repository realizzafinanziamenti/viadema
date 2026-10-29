<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Practice;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PracticeAttachments
{
    // Existing records contain paths relative to this disk, not the default disk.
    public const DISK = 'public';

    public function store(Practice $practice, UploadedFile $file): Attachment
    {
        $path = 'practice-attachments/'.$file->hashName();
        $stream = $file instanceof TemporaryUploadedFile
            ? $file->readStream()
            : fopen($file->getRealPath(), 'rb');

        if (! is_resource($stream)) {
            throw UnableToReadFile::fromLocation('temporary upload');
        }

        try {
            // Livewire storeAs ignores a false return from put when throw=false.
            if (! Storage::disk(self::DISK)->put($path, $stream)) {
                throw UnableToWriteFile::atLocation($path);
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $practice->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        $disk = Storage::disk(self::DISK);
        $path = $attachment->file_path;

        if (! $disk->exists($path)) {
            throw new FileNotFoundException;
        }

        $headers = [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'Content-Length' => $disk->size($path),
        ];
        // Open before returning the response so read failures reach the UI handler.
        $stream = $disk->readStream($path);
        if (! is_resource($stream)) {
            throw UnableToReadFile::fromLocation($path);
        }

        return response()->streamDownload(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, $attachment->file_name, $headers);
    }
}
