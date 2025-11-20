<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestCaseRequest extends FormRequest
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
        $testCase = $this->route('test_case') ?? $this->route('testCase');
        $testCaseId = is_object($testCase) ? $testCase->getKey() : $testCase;

        return [
            'case_key' => ['nullable', 'string', 'max:50', 'unique:test_cases,case_key,' . $testCaseId],
            'title' => ['required', 'string', 'max:255'],
            'folder_id' => ['nullable', 'exists:folders,id'],
            'preconditions' => ['nullable', 'string'],
            'steps' => ['required', 'string'],
            'expected_result' => ['required', 'string'],
            'acceptance_criteria' => ['nullable', 'string'],
            'additional_notes' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,ready,deprecated'],
            'custom_fields' => ['sometimes', 'array'],
            'custom_fields.*' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'case_key' => $this->input('case_key') ?: null,
        ]);
    }
}
