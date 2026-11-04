<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\FormDocument;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class FormDocumentTrash
{
    public function restore(FormDocument $document): void
    {
        DB::transaction(function () use ($document): void {
            $document = FormDocument::onlyTrashed()->lockForUpdate()->findOrFail($document->id);
            $attachment = $document->attachment;
            if (! $attachment || ! $this->validPath($attachment->file_path)
                || ! Storage::disk('public')->exists($attachment->file_path)) {
                throw new FileNotFoundException('File del modulo non disponibile: ripristino annullato.');
            }
            if (! $document->restore()) {
                throw new RuntimeException('Ripristino del modulo non riuscito.');
            }
        });
    }

    public function forceDelete(FormDocument $document): void
    {
        DB::transaction(function () use ($document): void {
            $document = FormDocument::onlyTrashed()->lockForUpdate()->findOrFail($document->id);
            $attachments = $document->attachment()->withTrashed()->get();
            foreach ($attachments as $attachment) {
                $path = $attachment->file_path;
                if (! $this->validPath($path) || Attachment::withTrashed()
                    ->where('file_path', $path)->where('id', '!=', $attachment->id)->exists()) {
                    throw new RuntimeException('Percorso del modulo non valido o condiviso.');
                }
                $disk = Storage::disk('public');
                if ($disk->exists($path) && (! $disk->delete($path) || $disk->exists($path))) {
                    throw new RuntimeException('Eliminazione del file del modulo non riuscita.');
                }
                // The file was checked and removed above; bypass the generic attachment observer.
                $attachment->forceDeleteQuietly();
            }
            if (! $document->forceDelete()) {
                throw new RuntimeException('Eliminazione del modulo non riuscita.');
            }
        });
    }

    private function validPath(?string $path): bool
    {
        return is_string($path) && preg_match('~^documents/[^/\\\\\\\\\x00-\x1F]+$~D', $path)
            && ! in_array(substr($path, 10), ['.', '..'], true);
    }
}
