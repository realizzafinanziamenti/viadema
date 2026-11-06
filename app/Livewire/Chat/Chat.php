<?php

namespace App\Livewire\Chat;

use Namu\WireChat\Models\Conversation;

class Chat extends \Namu\WireChat\Livewire\Chat\Chat
{
    protected function authorizeMessageDeletion(string $id): void
    {
        $this->authorizeConversationAdministration();
        // Scope the encrypted message to the conversation currently open.
        abort_unless($this->conversation->messages()->whereKey(decrypt($id))->exists(), 404);
    }

    public function deleteForMe(string $id): void
    {
        $this->authorizeMessageDeletion($id);
        parent::deleteForMe($id);
    }

    public function deleteForEveryone(string $id): void
    {
        $this->authorizeMessageDeletion($id);
        parent::deleteForEveryone($id);
    }

    protected function authorizeConversationAdministration(): void
    {
        abort_unless(auth()->user()?->hasRole('superadmin'), 403);
        $this->conversation = Conversation::findOrFail($this->conversation->getKey());
        abort_unless(auth()->user()->belongsToConversation($this->conversation), 403);
    }

    public function deleteConversation()
    {
        $this->authorizeConversationAdministration();

        return parent::deleteConversation();
    }

    public function clearConversation()
    {
        $this->authorizeConversationAdministration();

        return parent::clearConversation();
    }

    public function deleteAttachment(int $id): void
    {
        app(\App\Services\Chat\AttachmentService::class)->delete($id, (int) $this->conversation->getKey());
        $this->loadMessages();
    }

    public function getListeners()
    {
        return array_merge(parent::getListeners(), [
            'echo-private:conversation.'.$this->conversation?->id.',.chat.attachment-removed' => 'loadMessages',
        ]);
    }
}
