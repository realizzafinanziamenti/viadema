<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Namu\WireChat\Models\Attachment;

class PrivatizeChatAttachments extends Command
{
    protected $signature = 'chat:privatize-attachments';

    protected $description = 'Move registered WireChat files to private storage, verifying content before removing public copies';

    public function handle(): int
    {
        $source = Storage::disk('public');
        $target = Storage::disk('chat');
        $failed = false;
        // Includes group photos: WireChat uses the same disk for all attachments.
        foreach (Attachment::query()->cursor() as $attachment) {
            $path = $attachment->file_path;
            if (! is_string($path) || ! str_starts_with($path, 'attachments/')
                || preg_match('~(^|/)\.\.?(/|$)|\\\\|\x00~', $path)) {
                $this->error("Invalid path for attachment {$attachment->id}");
                $failed = true;

                continue;
            }
            if (! $source->exists($path)) {
                if (! $target->exists($path)) {
                    $this->error("Missing file for attachment {$attachment->id}");
                    $failed = true;
                }

                continue;
            }
            $stream = $source->readStream($path);
            try {
                $target->put($path, $stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
            if (hash_file('sha256', $source->path($path)) !== hash_file('sha256', $target->path($path))) {
                $this->error("Verification failed for attachment {$attachment->id}");
                $failed = true;

                continue;
            }
            if (! $source->delete($path)) {
                $this->error("Public copy still exists for attachment {$attachment->id}");
                $failed = true;
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
