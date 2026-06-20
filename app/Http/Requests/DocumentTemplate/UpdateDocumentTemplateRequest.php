<?php

namespace App\Http\Requests\DocumentTemplate;

use App\Enums\DocumentType;
use App\Http\Requests\Concerns\SanitizesRequestInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentTemplateRequest extends FormRequest
{
    use SanitizesRequestInput;

    public function authorize(): bool
    {
        return $this->user()?->can('ubah_template') ?? false;
    }

    public function rules(): array
    {
        $templateId = $this->route('documentTemplate')?->id;

        return [
            'nama_template' => ['required', 'string', 'max:255'],
            'kode_template' => ['required', 'string', 'max:100', Rule::unique('document_templates', 'kode_template')->ignore($templateId)],
            'tipe_dokumen' => ['required', Rule::enum(DocumentType::class)],
            'blade_file' => ['required', 'string', 'max:255'],
            'is_default' => ['boolean'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->sanitizedStrings([
            'nama_template',
            'kode_template',
            'blade_file',
            'keterangan',
        ]));
    }
}
