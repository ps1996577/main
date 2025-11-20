<?php

namespace App\Exports;

use App\Models\CustomField;
use App\Models\TestCase;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TestCasesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
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

    public function __construct(?int $folderId = null)
    {
        $this->folderId = $folderId;
        $this->customFields = CustomField::orderBy('position')->get();
    }

    public function collection(): Collection
    {
        $query = TestCase::with(['folder', 'customFieldValues']);

        if ($this->folderId) {
            $query->where('folder_id', $this->folderId);
        }

        return $query->orderBy('case_key')
            ->get()
            ->map(function (TestCase $testCase) {
                $row = [
                    $testCase->case_key,
                    $testCase->title,
                    $testCase->folder?->breadcrumb ?? '',
                    $testCase->status,
                    $testCase->preconditions,
                    $testCase->steps,
                    $testCase->expected_result,
                    $testCase->acceptance_criteria,
                    $testCase->additional_notes,
                ];

                foreach ($this->customFields as $field) {
                    $row[] = $testCase->getCustomFieldValue($field->id);
                }

                return $row;
            });
    }

    public function headings(): array
    {
        return array_merge(
            $this->baseHeadings,
            $this->customFields->pluck('name')->toArray()
        );
    }
}
