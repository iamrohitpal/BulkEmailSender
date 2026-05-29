<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'name' => 'required|string|max:255',
            'smtp_setting_id' => 'nullable|exists:smtp_settings,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'csv_file' => ($isCreate ? 'required' : 'nullable') . '|file|mimes:csv,txt|max:10240', // 10MB limit
            'resume_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // 5MB limit
            'resume_link' => 'nullable|url|max:2048',
            'delay_seconds' => 'nullable|integer|min:0|max:60',
        ];
    }
}
