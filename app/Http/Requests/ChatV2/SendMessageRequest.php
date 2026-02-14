<?php

namespace App\Http\Requests\ChatV2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:text,image,file,audio'],
            'body' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:10240'],
            'duration_ms' => ['nullable', 'integer', 'min:0', 'max:3600000'],
            'reply_to_message_id' => ['nullable', 'integer', 'exists:chat_v2_messages,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('type');
            $body = trim((string) $this->input('body'));
            $attachment = $this->file('attachment');

            if ($type === 'text' && $body === '') {
                $validator->errors()->add('body', 'Text messages require content.');
            }

            if ($type !== 'text' && ! $attachment) {
                $validator->errors()->add('attachment', 'Attachment is required for non-text messages.');
            }

            if (! $attachment) {
                return;
            }

            $mime = strtolower((string) $attachment->getMimeType());
            $allowed = match ($type) {
                'image' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                'audio' => ['audio/webm', 'audio/ogg', 'audio/mpeg', 'audio/wav', 'audio/mp4', 'audio/x-m4a'],
                'file' => ['application/pdf', 'text/plain', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword'],
                default => [],
            };

            if ($allowed !== [] && ! in_array($mime, $allowed, true)) {
                $validator->errors()->add('attachment', 'Invalid file type for selected message type.');
            }
        });
    }
}
