<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <x-symfonia-logo class="hidden sm:flex" />
                <div>
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-400">Symfonia · Quality Suite</p>
                    <h2 class="font-semibold text-2xl text-slate-900 leading-tight">
                        {{ __('Przypadki testowe') }}
                    </h2>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('import-export.index') }}" class="btn-emerald bg-white text-emerald-700 border border-emerald-200 hover:bg-emerald-50">
                    <i class="bi bi-arrow-repeat"></i>
                    Import / Export
                </a>
                <a href="{{ route('test-cases.create') }}" class="btn-emerald">
                    <i class="bi bi-plus-circle"></i>
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

    <div class="py-8 bg-slate-50/70">
        <div class="mx-auto w-full max-w-7xl px-4 lg:px-6 space-y-6">
            <section class="glass-card border border-emerald-50 px-6 py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm text-slate-500">{{ $metrics['total'] }} przypadków w aktualnym zestawie</p>
                        <div class="mt-3 flex flex-wrap gap-3 text-xs font-semibold">
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">
                                <i class="bi bi-check2-circle"></i>
                                {{ $metrics['ready'] }} gotowych
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-amber-700">
                                <i class="bi bi-pencil"></i>
                                {{ $metrics['draft'] }} szkiców
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-slate-600">
                                <i class="bi bi-archive"></i>
                                {{ $metrics['deprecated'] }} wycofanych
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('folders.index') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-white">
                            <i class="bi bi-folder2-open"></i>
                            Zarządzaj folderami
                        </a>
                        <a href="{{ route('import-export.export') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-white">
                            <i class="bi bi-download"></i>
                            Eksport CSV
                        </a>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_260px]">
                <div class="space-y-6">
                    <section class="glass-card border border-white/80">
                        <div class="border-b border-slate-100 px-6 py-4 flex flex-wrap items-center gap-3 justify-between">
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-slate-800">Akcje testowe</p>
                                <p class="text-xs text-slate-500">Szybkie zarządzanie przypadkami</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-sm font-semibold">
                                <a href="{{ route('test-cases.create') }}" class="btn-emerald">
                                    <i class="bi bi-plus-lg"></i> Nowy przypadek
                                </a>
                                <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-slate-600 hover:bg-white">
                                    <i class="bi bi-layers"></i> Klonuj
                                </button>
                                <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-slate-600 hover:bg-white">
                                    <i class="bi bi-play-circle"></i> Uruchom serię
                                </button>
                            </div>
                        </div>
                        <form method="GET" class="p-6 space-y-4">
                            <div class="grid gap-4 lg:grid-cols-12">
                                <div class="lg:col-span-5">
                                    <x-input-label for="search" value="Słowa kluczowe" />
                                    <x-text-input id="search" name="search" type="text" class="mt-1 block w-full"
                                                  :value="request('search')" placeholder="ID, tytuł, oczekiwany rezultat..." />
                                </div>
                                <div class="lg:col-span-3">
                                    <x-input-label for="folder_id" value="Folder" />
                                    <select id="folder_id" name="folder_id" class="mt-1 block w-full rounded-xl border-slate-200 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-sm">
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
                                    <select id="status" name="status" class="mt-1 block w-full rounded-xl border-slate-200 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                        <option value="">Dowolny</option>
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="lg:col-span-2">
                                    <x-input-label for="sort" value="Sortowanie" />
                                    <select id="sort" name="sort" class="mt-1 block w-full rounded-xl border-slate-200 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                        @foreach($sortOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 justify-between">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($activeChips->isEmpty())
                                        <span class="text-xs font-semibold text-slate-400">Brak aktywnych filtrów</span>
                                    @else
                                        @foreach($activeChips as $label => $value)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                <span>{{ $label }}:</span>
                                                <span class="truncate max-w-[140px]">{{ $value }}</span>
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <a href="{{ route('test-cases.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Wyczyść</a>
                                    <button type="submit" class="btn-emerald">
                                        <i class="bi bi-funnel"></i>
                                        Zastosuj filtry
                                    </button>
                                </div>
                            </div>
                        </form>
                    </section>

                    <section class="glass-card border border-white/80">
                        <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Lista przypadków</p>
                                <p class="text-sm text-slate-500">
                                    {{ $testCases->total() }} wyników · Strona {{ $testCases->currentPage() }} z {{ $testCases->lastPage() }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-sm font-semibold">
                                <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-slate-600 hover:bg-white">
                                    <i class="bi bi-copy"></i> Duplikuj
                                </button>
                                <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-slate-600 hover:bg-white">
                                    <i class="bi bi-trash"></i> Usuń zaznaczone
                                </button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-white/80">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-widest">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-widest">Tytuł</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-widest">Folder</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-widest">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-widest">Aktualizacja</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-widest">Akcje</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($testCases as $case)
                                        <tr class="hover:bg-emerald-50/40">
                                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $case->case_key }}</td>
                                            <td class="px-6 py-4">
                                                <a href="{{ route('test-cases.show', $case) }}" class="text-base font-semibold text-slate-900 hover:text-emerald-600">
                                                    {{ $case->title }}
                                                </a>
                                                <p class="mt-1 text-sm text-slate-600">
                                                    {{ \Illuminate\Support\Str::limit($case->expected_result ?: $case->steps ?: 'Brak opisu', 120) }}
                                                </p>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-600">
                                                {{ optional($case->folder)->breadcrumb ?? 'Brak folderu' }}
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                <x-status-badge :status="$case->status" />
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-500">
                                                {{ $case->updated_at?->diffForHumans() }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-right">
                                                <div class="flex flex-col gap-2 items-end">
                                                    <a href="{{ route('test-cases.show', $case) }}" class="text-emerald-600 hover:text-emerald-700 font-semibold">
                                                        <i class="bi bi-eye"></i> Podgląd
                                                    </a>
                                                    <a href="{{ route('test-cases.edit', $case) }}" class="text-slate-700 hover:text-slate-900 font-semibold">
                                                        <i class="bi bi-pencil"></i> Edytuj
                                                    </a>
                                                    <form method="POST" action="{{ route('test-cases.destroy', $case) }}" class="inline" onsubmit="return confirm('Usunąć ten przypadek?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-rose-600 hover:text-rose-700 font-semibold">
                                                            <i class="bi bi-x-circle"></i> Usuń
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">Brak przypadków spełniających kryteria.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 border-t border-slate-100">
                            {{ $testCases->links() }}
                        </div>
                    </section>
                </div>

                <aside class="space-y-4 lg:sticky lg:top-24">
                    <section class="glass-card border border-white/80 p-5 space-y-4" x-data="{}">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Mapa testów</p>
                                <h3 class="text-lg font-semibold text-slate-900">Foldery</h3>
                            </div>
                            <span class="text-xs font-semibold text-slate-500">{{ $metrics['total'] }} TC</span>
                        </div>
                        <div class="space-y-3 text-sm text-slate-600">
                            <a href="{{ route('test-cases.index', $folderFilterQuery) }}"
                               class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-emerald-600">
                                <span class="inline-flex h-2 w-2 rounded-full bg-slate-300"></span>
                                Wszystkie foldery
                            </a>
                            @if($folderTree->isNotEmpty())
                                <div class="space-y-2 max-h-[520px] overflow-y-auto pr-1 custom-scroll">
                                    @include('test-cases.partials.folder-tree', [
                                        'nodes' => $folderTree,
                                        'activeFolderId' => request('folder_id'),
                                        'folderFilterQuery' => $folderFilterQuery,
                                    ])
                                </div>
                            @else
                                <p class="text-slate-500">Nie zdefiniowano folderów.</p>
                            @endif
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
