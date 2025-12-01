<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500">Widok pakietu testowego · {{ $metrics['total'] }} przypadków</p>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    {{ __('Przypadki testowe') }}
                </h2>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('import-export.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-emerald-700 rounded-full border border-emerald-200 bg-white/60 backdrop-blur hover:bg-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16M4 12h16M4 17h16" /></svg>
                    Import / Export
                </a>
                <a href="{{ route('test-cases.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2 bg-emerald-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-emerald-500/30 hover:bg-emerald-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" /></svg>
                    Dodaj przypadek
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $activeChips = collect([
            'Szukaj' => request('search'),
            'Folder' => request('folder_id') ? optional($folders->firstWhere('id', (int) request('folder_id')))->breadcrumb : null,
            'Status' => request('status') ? ($statuses[request('status')] ?? request('status')) : null,
            'Sortowanie' => request('sort') ? ($sortOptions[request('sort')] ?? request('sort')) : null,
        ])->filter();

        $folderFilterQuery = request()->except(['page', 'folder_id']);
    @endphp

    <div class="py-6">
        <div class="mx-auto w-full max-w-[1600px] px-4 lg:px-8 space-y-6">
            <section class="rounded-3xl border border-white/60 bg-white/70 backdrop-blur-lg shadow-sm">
                <div class="grid gap-6 lg:grid-cols-[320px,1fr]">
                    <aside class="border-r border-white/50 px-6 py-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-gray-400">Struktura</p>
                                <h3 class="text-lg font-semibold text-gray-900">Foldery i przypadki</h3>
                            </div>
                            <a href="{{ route('folders.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Zarządzaj</a>
                        </div>
                        <div class="mt-4 space-y-3 text-sm text-gray-600" x-data="{}">
                            <a href="{{ route('test-cases.index', $folderFilterQuery) }}"
                               class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-emerald-600">
                                <span class="inline-flex h-2 w-2 rounded-full bg-gray-300"></span>
                                Wszystkie foldery
                            </a>
                            @if($folderTree->isNotEmpty())
                                <div class="space-y-2">
                                    @include('test-cases.partials.folder-tree', [
                                        'nodes' => $folderTree,
                                        'activeFolderId' => request('folder_id'),
                                        'folderFilterQuery' => $folderFilterQuery,
                                    ])
                                </div>
                            @else
                                <p class="text-gray-500">Nie zdefiniowano folderów.</p>
                            @endif
                        </div>
                    </aside>

                    <div class="px-6 py-6 space-y-6">
                        <div class="flex flex-wrap items-center gap-3 justify-between border border-white/70 rounded-2xl px-4 py-3 bg-white/60">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-widest text-gray-400">
                                <span>{{ $testCases->total() }} przypadków</span>
                                <span class="hidden sm:inline-flex h-1 w-1 rounded-full bg-gray-300"></span>
                                <span class="text-gray-500">Widok listy</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('test-cases.create') }}" class="inline-flex items-center gap-2 rounded-full border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-white">
                                    + Nowy przypadek
                                </a>
                                <a href="{{ route('import-export.export') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-white">
                                    Eksportuj
                                </a>
                                <a href="{{ route('import-export.index') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-white">
                                    Importuj
                                </a>
                            </div>
                        </div>

                        <section class="rounded-2xl border border-white/70 bg-white/60 backdrop-blur">
                            <form method="GET" class="p-5 space-y-4">
                                <div class="grid gap-4 lg:grid-cols-12">
                                    <div class="lg:col-span-5">
                                        <x-input-label for="search" value="Słowa kluczowe" />
                                        <x-text-input id="search" name="search" type="text" class="mt-1 block w-full"
                                                      :value="request('search')" placeholder="ID, tytuł, oczekiwany rezultat..." />
                                    </div>
                                    <div class="lg:col-span-3">
                                        <x-input-label for="folder_id" value="Folder" />
                                        <select id="folder_id" name="folder_id" class="mt-1 block w-full rounded-xl border-gray-200 bg-white/80 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                            <option value="">Dowolny</option>
                                            @foreach($folders as $folder)
                                                <option value="{{ $folder->id }}" @selected((int) request('folder_id') === $folder->id)>
                                                    {{ $folder->breadcrumb ?? $folder->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="lg:col-span-2">
                                        <x-input-label for="status" value="Status" />
                                        <select id="status" name="status" class="mt-1 block w-full rounded-xl border-gray-200 bg-white/80 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                            <option value="">Dowolny</option>
                                            @foreach($statuses as $value => $label)
                                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="lg:col-span-2">
                                        <x-input-label for="sort" value="Sortowanie" />
                                        <select id="sort" name="sort" class="mt-1 block w-full rounded-xl border-gray-200 bg-white/80 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                            @foreach($sortOptions as $value => $label)
                                                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-3 justify-between">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if($activeChips->isEmpty())
                                            <span class="text-xs font-semibold text-gray-400">Brak aktywnych filtrów</span>
                                        @else
                                            @foreach($activeChips as $label => $value)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50/80 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                    <span>{{ $label }}:</span>
                                                    <span class="truncate max-w-[140px]">{{ $value }}</span>
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <a href="{{ route('test-cases.index') }}" class="text-sm text-gray-500 hover:text-emerald-600">Wyczyść</a>
                                        <button type="submit" class="inline-flex items-center justify-center px-6 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-full shadow hover:bg-emerald-500">
                                            Zastosuj filtry
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </section>

                        <section class="rounded-2xl border border-white/70 bg-white/70 backdrop-blur">
                            <div class="px-6 py-4 border-b border-white/60 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Lista przypadków</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $testCases->total() }} wyników · Strona {{ $testCases->currentPage() }} z {{ $testCases->lastPage() }}
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-sm font-semibold">
                                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-gray-600 hover:bg-white">Duplikuj</button>
                                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-gray-600 hover:bg-white">Usuń zaznaczone</button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200/70">
                                    <thead class="bg-white/60">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-widest">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-widest">Tytuł</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-widest">Folder</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-widest">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-widest">Aktualizacja</th>
                                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-widest">Akcje</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100/70">
                                        @forelse($testCases as $case)
                                            <tr class="hover:bg-emerald-50/40">
                                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $case->case_key }}</td>
                                                <td class="px-6 py-4">
                                                    <a href="{{ route('test-cases.show', $case) }}" class="text-base font-semibold text-gray-900 hover:text-emerald-600">
                                                        {{ $case->title }}
                                                    </a>
                                                    <p class="mt-1 text-sm text-gray-600">
                                                        {{ \Illuminate\Support\Str::limit($case->expected_result ?: $case->steps ?: 'Brak opisu', 120) }}
                                                    </p>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-600">
                                                    {{ optional($case->folder)->breadcrumb ?? 'Brak folderu' }}
                                                </td>
                                                <td class="px-6 py-4 text-sm">
                                                    <x-status-badge :status="$case->status" />
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500">
                                                    {{ $case->updated_at?->diffForHumans() }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-right">
                                                    <div class="flex flex-col gap-2 items-end">
                                                        <a href="{{ route('test-cases.show', $case) }}" class="text-emerald-600 hover:text-emerald-700 font-semibold">Podgląd</a>
                                                        <a href="{{ route('test-cases.edit', $case) }}" class="text-emerald-700 hover:text-emerald-800 font-semibold">Edytuj</a>
                                                        <form method="POST" action="{{ route('test-cases.destroy', $case) }}" class="inline" onsubmit="return confirm('Usunąć ten przypadek?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-rose-600 hover:text-rose-700 font-semibold">Usuń</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">Brak przypadków spełniających kryteria.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="px-6 py-4 border-t border-white/60">
                                {{ $testCases->links() }}
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
