<?php

namespace App\Http\Controllers;

use App\Http\Requests\FolderRequest;
use App\Models\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FolderController extends Controller
{
    public function index(): View
    {
        $folders = Folder::with([
                'children' => fn ($query) => $query->withCount('testCases'),
            ])
            ->withCount('testCases')
            ->roots()
            ->orderBy('name')
            ->get();

        $folderOptions = Folder::with('parent')->orderBy('name')->get();

        return view('folders.index', compact('folders', 'folderOptions'));
    }

    public function create(): View
    {
        $folders = Folder::orderBy('name')->get();

        return view('folders.create', compact('folders'));
    }

    public function store(FolderRequest $request): RedirectResponse
    {
        Folder::create($request->validated() + [
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('folders.index')
            ->with('status', 'Folder został dodany.');
    }

    public function show(Folder $folder): View
    {
        $folder->load(['children', 'parent', 'testCases' => fn ($query) => $query->latest()]);

        return view('folders.show', compact('folder'));
    }

    public function edit(Folder $folder): View
    {
        $folders = Folder::where('id', '!=', $folder->id)->orderBy('name')->get();

        return view('folders.edit', compact('folder', 'folders'));
    }

    public function update(FolderRequest $request, Folder $folder): RedirectResponse
    {
        $folder->update($request->validated());

        return redirect()->route('folders.index')
            ->with('status', 'Folder został zaktualizowany.');
    }

    public function destroy(Folder $folder): RedirectResponse
    {
        $folder->delete();

        return redirect()->route('folders.index')
            ->with('status', 'Folder został usunięty.');
    }
}
