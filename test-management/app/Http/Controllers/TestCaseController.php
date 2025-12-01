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
        $baseQuery = TestCase::query();

        if ($search = $request->string('search')->toString()) {
            $baseQuery->where(function (Builder $builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('case_key', 'like', "%{$search}%")
                    ->orWhere('expected_result', 'like', "%{$search}%");
            });
        }

        if ($folderId = $request->integer('folder_id')) {
            $baseQuery->where('folder_id', $folderId);
        }

        if ($status = $request->string('status')->toString()) {
            $baseQuery->where('status', $status);
        }

        $sortConfig = [
            'recent' => ['label' => 'Ostatnie aktualizacje', 'column' => 'updated_at', 'direction' => 'desc'],
            'oldest' => ['label' => 'Najstarsze aktualizacje', 'column' => 'updated_at', 'direction' => 'asc'],
            'title' => ['label' => 'Nazwa A-Z', 'column' => 'title', 'direction' => 'asc'],
            'key' => ['label' => 'ID rosnąco', 'column' => 'case_key', 'direction' => 'asc'],
        ];

        $sort = $request->string('sort')->toString();
        if (! array_key_exists($sort, $sortConfig)) {
            $sort = 'recent';
        }

        $sortOptions = collect($sortConfig)
            ->mapWithKeys(static fn (array $config, string $key) => [$key => $config['label']])
            ->all();

        $testCases = (clone $baseQuery)
            ->with('folder')
            ->orderBy($sortConfig[$sort]['column'], $sortConfig[$sort]['direction'])
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $statusBreakdown = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $statuses = [
            'draft' => 'Szkic',
            'ready' => 'Gotowy',
            'deprecated' => 'Wycofany',
        ];

        $metrics = [
            'total' => (int) $testCases->total(),
            'ready' => (int) ($statusBreakdown['ready'] ?? 0),
            'draft' => (int) ($statusBreakdown['draft'] ?? 0),
            'deprecated' => (int) ($statusBreakdown['deprecated'] ?? 0),
        ];

        $folders = Folder::orderBy('name')->get();
        $folderTree = Folder::roots()
            ->with([
                'children',
                'testCases' => static fn ($query) => $query
                    ->select('id', 'folder_id', 'case_key', 'title', 'status', 'updated_at')
                    ->orderBy('case_key'),
            ])
            ->withCount('testCases')
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return view('test-cases.index', [
            'testCases' => $testCases,
            'folders' => $folders,
            'folderTree' => $folderTree,
            'statuses' => $statuses,
            'statusBreakdown' => $statusBreakdown,
            'metrics' => $metrics,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
        ]);
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
