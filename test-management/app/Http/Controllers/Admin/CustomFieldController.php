<?php

namespace App\Http\Controllers.Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomFieldRequest;
use App\Models\CustomField;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomFieldController extends Controller
{
    public function index(): View
    {
        $fields = CustomField::orderBy('position')->orderBy('name')->paginate(20);

        return view('admin.custom-fields.index', compact('fields'));
    }

    public function create(): View
    {
        return view('admin.custom-fields.create');
    }

    public function store(CustomFieldRequest $request): RedirectResponse
    {
        CustomField::create($request->validated() + [
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.custom-fields.index')
            ->with('status', 'Pole zostało dodane.');
    }

    public function edit(CustomField $customField): View
    {
        return view('admin.custom-fields.edit', [
            'customField' => $customField,
        ]);
    }

    public function update(CustomFieldRequest $request, CustomField $customField): RedirectResponse
    {
        $customField->update($request->validated());

        return redirect()
            ->route('admin.custom-fields.index')
            ->with('status', 'Pole zostało zaktualizowane.');
    }

    public function destroy(CustomField $customField): RedirectResponse
    {
        $customField->delete();

        return redirect()
            ->route('admin.custom-fields.index')
            ->with('status', 'Pole zostało usunięte.');
    }
}