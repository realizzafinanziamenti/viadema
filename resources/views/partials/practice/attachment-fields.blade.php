    {{-- Attachments --}}
    <div class="flex flex-col gap-6 col-span-2">

        {{-- Existing Attachments --}}
        @if ($existingAttachments->isNotEmpty())
            <div class="flex flex-col gap-1.5">
                <flux:label>
                    Allegati esistenti
                </flux:label>

                @foreach ($existingAttachments as $attachment)
                    <x-display-file
                        :attachment="$attachment"
                        :can-delete="$canDeleteAttachments ?? Gate::allows('delete', $attachmentOwner)"
                        value="{{ $attachment->file_name }}"
                    />
                @endforeach
            </div>
        @endif

        @if ($canUploadAttachments ?? true)
        {{-- Temporary Attachments --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>
                Allegati
            </flux:label>

            <x-upload-files-container
                model="temporaryFiles"
                :has-error="$errors->has('temporaryFiles.*')"
                multiple
            />

            <flux:error name="temporaryFiles" />
            <flux:error name="temporaryFiles.*" />
            <flux:error name="{{ $attachmentField }}" />
            <flux:error name="{{ $attachmentField }}.*" />

            <div class="text-xs text-gray-custom-4">
                <div>- Max 10MB per file</div>
                <div>- Max 10 file per volta</div>
                <div>
                    - Formati accettati:
                    jpg, png, pdf, doc, docx, xls, xlsm, xlsx, csv
                </div>
            </div>
        </div>

        {{-- Temporary Attachments Preview --}}
        @if (!empty($pendingAttachments))
            <div class="flex flex-col gap-1.5">
                <flux:label class="font-semibold">
                    Allegati temporanei caricati
                    {{ count($pendingAttachments) }}/{{ count($pendingAttachments) }}
                </flux:label>

                @foreach ($pendingAttachments as $index => $attachment)
                    <x-display-preview-file
                        :attachment="$attachment"
                        :index="$index"
                    />
                @endforeach
            </div>
        @endif

        @endif
    </div>
