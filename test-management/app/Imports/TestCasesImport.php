<?php

namespace App\Imports;

use App\Models\CustomField;
use App\Models\Folder;
use App\Models\TestCase;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TestCasesImport implements ToCollection, WithHeadingRow
{
    protected User $user;

    protected ?int $defaultFolderId;

    protected Collection $customFields;

    public function __construct(User $user, ?int $defaultFolderId = null)
    {
        $this->user = $user;
        $this->defaultFolderId = $defaultFolderId;
        $this->customFields = CustomField::orderBy('position')->get();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $normalized = collect($row)->mapWithKeys(function ($value, $key) {
                $normalizedKey = Str::of($key)->trim()->lower()->value();

                return [$normalizedKey => $value];
            });

            if ($normalized->filter(fn ($value) => filled($value))->isEmpty()) {
                continue;
            }

            $caseKey = $normalized->get('id przypadku testowego') ?? $normalized->get('case_key');
            $title = $normalized->get('cel testu') ?? $normalized->get('title');

            if (blank($title)) {
                continue;
            }

            $status = $normalized->get('status', 'draft');
            $status = in_array($status, ['draft', 'ready', 'deprecated'], true) ? $status : 'draft';

            $folderPath = $normalized->get('folder') ?? $normalized->get('folder_path');
            $folderId = $this->resolveFolderId($folderPath) ?? $this->defaultFolderId;

            $attributes = [
                'title' => $title,
                'folder_id' => $folderId,
                'preconditions' => $normalized->get('wymagania wstępne') ?? $normalized->get('preconditions'),
                'steps' => $normalized->get('kroki testowe') ?? $normalized->get('steps') ?? '',
                'expected_result' => $normalized->get('oczekiwany rezultat') ?? $normalized->get('expected_result') ?? '',
                'acceptance_criteria' => $normalized->get('kryteria zaliczenia') ?? $normalized->get('acceptance_criteria'),
                'additional_notes' => $normalized->get('uwagi dodatkowe') ?? $normalized->get('notes'),
                'status' => $status,
                'updated_by' => $this->user->id,
            ];

            if ($caseKey) {
                $identity = $this->user->isAdmin()
                    ? ['case_key' => $caseKey]
                    : ['case_key' => $caseKey, 'created_by' => $this->user->id];

                $testCase = TestCase::updateOrCreate($identity, $attributes + [
                    'created_by' => $this->user->id,
                ]);
            } else {
                $testCase = TestCase::create($attributes + [
                    'created_by' => $this->user->id,
                ]);
            }

            $customValues = $this->extractCustomValues($row);
            $this->syncCustomFields($testCase, $customValues);
        }
    }

    protected function resolveFolderId(?string $folderPath): ?int
    {
        if (blank($folderPath)) {
            return null;
        }

        $segments = collect(explode('/', $folderPath))
            ->map(fn ($segment) => trim($segment))
            ->filter();

        $parentId = null;

        foreach ($segments as $segment) {
            $lookup = [
                'name' => $segment,
                'parent_id' => $parentId,
            ];

            // SECURITY: isolate folder trees per user unless admin.
            if (! $this->user->isAdmin()) {
                $lookup['created_by'] = $this->user->id;
            }

            $folder = Folder::firstOrCreate($lookup, [
                'created_by' => $this->user->id,
            ]);

            $parentId = $folder->id;
        }

        return $parentId;
    }

    protected function extractCustomValues(array $row): array
    {
        $values = [];

        foreach ($this->customFields as $field) {
            foreach ($row as $column => $value) {
                if (strcasecmp($column, $field->name) === 0) {
                    $values[$field->id] = $value;
                }
            }
        }

        return $values;
    }

    protected function syncCustomFields(TestCase $testCase, array $values): void
    {
        foreach ($values as $fieldId => $value) {
            if (blank($value)) {
                $testCase->customFieldValues()->where('custom_field_id', $fieldId)->delete();
                continue;
            }

            $testCase->customFieldValues()->updateOrCreate(
                ['custom_field_id' => $fieldId],
                ['value' => $value]
            );
        }
    }
}
