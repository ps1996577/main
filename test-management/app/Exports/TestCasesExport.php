<?php

namespace App\Exports;

use App\Models\CustomField;
use App\Models\TestCase;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TestCasesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected User $user;

    protected ?int $folderId;

    protected Collection $customFields;

    protected array $baseHeadings = [
        'ID przypadku testowego',
        'Cel testu',
        'Folder',
        'Status',
        'Wymagania wstępne',
        'Kroki testowe',
        'Oczekiwany rezultat',
        'Kryteria zaliczenia',
        'Uwagi dodatkowe',
    ];

    public function __construct(User $user, ?int $folderId = null)
    {
        $this->user = $user;
        $this->folderId = $folderId;
        $this->customFields = CustomField::orderBy('position')->get();
    }

    public function collection(): Collection
    {
        $query = TestCase::with(['folder', 'customFieldValues']);

        if (! $this->user->isAdmin()) {
            $query->where('created_by', $this->user->id);
        }

        if ($this->folderId) {
            $query->where('folder_id', $this->folderId);
        }

        return $query->orderBy('case_key')
            ->get()
            ->map(function (TestCase $testCase) {
                $row = [
                    $this->sanitizeSpreadsheetValue($testCase->case_key),
                    $this->sanitizeSpreadsheetValue($testCase->title),
                    $this->sanitizeSpreadsheetValue($testCase->folder?->breadcrumb ?? ''),
                    $this->sanitizeSpreadsheetValue($testCase->status),
                    $this->sanitizeSpreadsheetValue($testCase->preconditions),
                    $this->sanitizeSpreadsheetValue($testCase->steps),
                    $this->sanitizeSpreadsheetValue($testCase->expected_result),
                    $this->sanitizeSpreadsheetValue($testCase->acceptance_criteria),
                    $this->sanitizeSpreadsheetValue($testCase->additional_notes),
                ];

                foreach ($this->customFields as $field) {
                    $row[] = $this->sanitizeSpreadsheetValue($testCase->getCustomFieldValue($field->id));
                }

                return $row;
            });
    }

    protected function sanitizeSpreadsheetValue(mixed $value): mixed
    {
        // Mitigate CSV/Excel formula injection. Treat user-provided strings starting
        // with special characters as literal strings by prefixing with a single quote.
        if (! is_string($value)) {
            return $value;
        }

        $trimmed = ltrim($value);
        if ($trimmed === '') {
            return $value;
        }

        $first = $trimmed[0];
        if (in_array($first, ['=', '+', '-', '@'], true)) {
            return "'".$value;
        }

        return $value;
    }

    public function headings(): array
    {
        return array_merge(
            $this->baseHeadings,
            $this->customFields->pluck('name')->toArray()
        );
    }
}
