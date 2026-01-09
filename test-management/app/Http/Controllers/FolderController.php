<?php

namespace App\Http\Controllers;

use App\Http\Requests\FolderRequest;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FolderController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $folders = Folder::with([
                'children' => fn ($query) => $query->withCount('testCases'),
            ])
            ->withCount('testCases')
            ->roots()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('created_by', $user->id))
            ->orderBy('name')
            ->get();

        $folderOptions = Folder::with('parent')
            ->when(! $user->isAdmin(), fn ($query) => $query->where('created_by', $user->id))
            ->orderBy('name')
            ->get();

        return view('folders.index', compact('folders', 'folderOptions'));
    }

    public function create(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $folders = Folder::when(! $user->isAdmin(), fn ($query) => $query->where('created_by', $user->id))
            ->orderBy('name')
            ->get();

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
        $this->authorizeFolder($folder);

        /** @var User $user */
        $user = auth()->user();

        $folder->load([
            'children' => fn ($query) => $query
                ->when(! $user->isAdmin(), fn ($childQuery) => $childQuery->where('created_by', $user->id)),
            'parent' => fn ($query) => $query
                ->when(! $user->isAdmin(), fn ($parentQuery) => $parentQuery->where('created_by', $user->id)),
            'testCases' => fn ($query) => $query
                ->when(! $user->isAdmin(), fn ($tcQuery) => $tcQuery->where('created_by', $user->id))
                ->latest(),
        ]);

        return view('folders.show', compact('folder'));
    }

    public function edit(Folder $folder): View
    {
        $this->authorizeFolder($folder);

        /** @var User $user */
        $user = auth()->user();

        $folders = Folder::where('id', '!=', $folder->id)
            ->when(! $user->isAdmin(), fn ($query) => $query->where('created_by', $user->id))
            ->orderBy('name')
            ->get();

        return view('folders.edit', compact('folder', 'folders'));
    }

    public function update(FolderRequest $request, Folder $folder): RedirectResponse
    {
        $this->authorizeFolder($folder);

        $folder->update($request->validated());

        return redirect()->route('folders.index')
            ->with('status', 'Folder został zaktualizowany.');
    }

    public function destroy(Folder $folder): RedirectResponse
    {
        $this->authorizeFolder($folder);

        $folder->delete();

        return redirect()->route('folders.index')
            ->with('status', 'Folder został usunięty.');
    }

    protected function authorizeFolder(Folder $folder): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        if ($user->isAdmin()) {
            return;
        }

        if ((int) $folder->created_by !== (int) $user->id) {
            abort(403);
        }
    }
}
