<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestCaseImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $folderExistsRule = $this->user()?->isAdmin()
            ? Rule::exists('folders', 'id')
            : Rule::exists('folders', 'id')->where('created_by', $this->user()?->id);

        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'folder_id' => ['nullable', $folderExistsRule],
        ];
    }
}
