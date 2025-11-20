<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestCaseRequest;
use App\Models\CustomField;
use App\Models\Folder;
use App\Models\TestCase;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestCaseController extends Controller
{
    public function index(Request $request): View
    {
        $query = TestCase::with('folder');

        if ($search = $request->string('search')->toString()) {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('case_key', 'like', "%{$search}%")
                    ->orWhere('expected_result', 'like', "%{$search}%");
            });
        }

        if ($folderId = $request->integer('folder_id')) {
            $query->where('folder_id', $folderId);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $testCases = $query->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $folders = Folder::orderBy('name')->get();
        $statuses = [
            'draft' => 'Szkic',
            'ready' => 'Gotowy',
            'deprecated' => 'Wycofany',
        ];

        return view('test-cases.index', compact('testCases', 'folders', 'statuses'));
    }

    public function create(Request $request): View
    {
        $folders = Folder::orderBy('name')->get();
        $customFields = CustomField::active()->get();
        $testCase = new TestCase([
            'folder_id' => $request->integer('folder_id'),
            'status' => 'draft',
        ]);

        return view('test-cases.create', compact('folders', 'customFields', 'testCase'));
    }

    public function store(TestCaseRequest $request): RedirectResponse
    {
        $payload = collect($request->validated());
        $customFields = $payload->pull('custom_fields', []);

        /** @var User $user */
        $user = $request->user();

        /** @var TestCase $testCase */
        $testCase = TestCase::create($payload->toArray() + [
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->syncCustomFields($testCase, $customFields);

        return redirect()
            ->route('test-cases.show', $testCase)
            ->with('status', 'Przypadek testowy został utworzony.');
    }

    public function show(TestCase $testCase): View
    {
        $testCase->load(['folder', 'creator', 'updater', 'customFieldValues.field']);
        $customFields = CustomField::orderBy('position')->get();

        return view('test-cases.show', [
            'testCase' => $testCase,
            'customFields' => $customFields,
        ]);
    }

    public function edit(TestCase $testCase): View
    {
        $testCase->load('customFieldValues');
        $folders = Folder::orderBy('name')->get();
        $customFields = CustomField::orderBy('position')->get();

        return view('test-cases.edit', compact('testCase', 'folders', 'customFields'));
    }

    public function update(TestCaseRequest $request, TestCase $testCase): RedirectResponse
    {
        $payload = collect($request->validated());
        $customFields = $payload->pull('custom_fields', []);

        /** @var User $user */
        $user = $request->user();

        $testCase->update($payload->toArray() + [
            'updated_by' => $user->id,
        ]);

        $this->syncCustomFields($testCase, $customFields);

        return redirect()
            ->route('test-cases.show', $testCase)
            ->with('status', 'Przypadek testowy został zaktualizowany.');
    }

    public function destroy(TestCase $testCase): RedirectResponse
    {
        $testCase->delete();

        return redirect()
            ->route('test-cases.index')
            ->with('status', 'Przypadek testowy został usunięty.');
    }

    protected function syncCustomFields(TestCase $testCase, array $values): void
    {
        $fields = CustomField::pluck('id')->all();

        $existingIds = $testCase->customFieldValues()
            ->pluck('custom_field_id', 'custom_field_id')
            ->keys()
            ->all();

        foreach ($fields as $fieldId) {
            $value = $values[$fieldId] ?? null;

            if (is_null($value) || $value === '') {
                if (in_array($fieldId, $existingIds, true)) {
                    $testCase->customFieldValues()
                        ->where('custom_field_id', $fieldId)
                        ->delete();
                }

                continue;
            }

            $testCase->customFieldValues()
                ->updateOrCreate(
                    ['custom_field_id' => $fieldId],
                    ['value' => $value]
                );
        }
    }
}
