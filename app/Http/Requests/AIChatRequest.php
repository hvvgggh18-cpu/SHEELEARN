<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AIChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Either a message or a file must be present. File is validated here as well.
            'message' => ['required_without:file', 'nullable', 'string', 'max:20000'],
            'file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,docx,doc,txt,pptx,xlsx,csv,png,jpg,jpeg,webp'],
            'mode' => ['nullable', 'string', 'in:general,explain,math,summarize,quiz,flashcards,essay,research,study'],
            'conversation_id' => ['nullable', 'integer', Rule::exists('chat_conversations', 'id')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'message' => trim($this->input('message', '')),
            'mode' => $this->input('mode', 'general') ?: 'general',
        ]);
    }
}
