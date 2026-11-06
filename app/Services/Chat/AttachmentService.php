<?php

namespace App\Services\Chat;

use Illuminate\Support\Facades\Storage;
use Namu\WireChat\Models\Attachment;
use Namu\WireChat\Models\Message;

final class AttachmentService
{
    public function authorized(int $id, ?int $conversationId = null): Attachment
    {
        $attachment = Attachment::findOrFail($id);
        $message = $attachment->attachable;
        abort_unless($message instanceof Message && $message->conversation, 404);
        abort_unless(auth()->user()?->belongsToConversation($message->conversation), 403);
        abort_if($conversationId !== null && $message->conversation_id !== $conversationId, 403);
        $path = $attachment->file_path;
        abort_unless(is_string($path) && str_starts_with($path, 'attachments/')
            && ! preg_match('~(^|/)\.\.?(/|$)|\\\\|\x00~', $path), 404);

        return $attachment;
    }

    public function download(int $id)
    {
        $attachment = $this->authorized($id);
        $disk = Storage::disk(config('wirechat.attachments.storage_disk'));
        abort_unless($disk->exists($attachment->file_path), 404);

        return $disk->download($attachment->file_path, basename($attachment->original_name));
    }

    public function delete(int $id, int $conversationId): void
    {
        $attachment = $this->authorized($id, $conversationId);
        $disk = Storage::disk(config('wirechat.attachments.storage_disk'));
        if ($disk->exists($attachment->file_path)) {
            abort_unless($disk->delete($attachment->file_path), 500, 'Impossibile eliminare il file.');
        }
        $attachment->delete();
        // Keep the message and its replies; only the selected document is removed.
        broadcast(new \App\Events\ChatAttachmentRemoved($conversationId));
    }
}
