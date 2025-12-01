<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500">Planowanie i egzekucja scenariuszy regresyjnych</p>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    {{ __('Przypadki testowe') }}
                </h2>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('import-export.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-emerald-200 text-sm font-semibold text-emerald-700 rounded-md hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                    Import / Export
                </a>
                <a href="{{ route('test-cases.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
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

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-dashboard-card label="Widoczne przypadki" :value="$metrics['total']" icon="stack" />
                <x-dashboard-card label="Gotowe" :value="$metrics['ready']" tone="success" icon="check" />
                <x-dashboard-card label="Szkice" :value="$metrics['draft']" tone="warning" icon="pencil" />
                <x-dashboard-card label="Wycofane" :value="$metrics['deprecated']" tone="danger" icon="archive" />
            </div>

            <div class="grid gap-6 lg:grid-cols-[360px,1fr]">
                <aside class="space-y-6">
                    <section class="bg-white shadow rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Foldery i przypadki</h3>
                                <p class="text-sm text-gray-500">Rozwiń folder, aby zobaczyć przypisane przypadki</p>
                            </div>
                            <a href="{{ route('folders.index') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">Zarządzaj</a>
                        </div>
                        <div class="mt-4 space-y-3 text-sm text-gray-600">
                            <a href="{{ route('test-cases.index', $folderFilterQuery) }}"
                               class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-emerald-600">
                                <span class="inline-flex h-2 w-2 rounded-full bg-gray-300"></span>
                                Wszystkie foldery
                            </a>

                            @if($folderTree->isNotEmpty())
                                <div class="space-y-2" x-data="{}">
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
                    </section>

                    <section class="bg-white shadow rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Statusy w filtrze</h3>
                            <span class="text-sm text-gray-500">{{ $metrics['total'] }} widocznych</span>
                        </div>
                        <div class="mt-4 space-y-4">
                            @foreach($statuses as $value => $label)
                                @php
                                    $count = $statusBreakdown[$value] ?? 0;
                                    $percentage = $metrics['total'] > 0 ? round(($count / $metrics['total']) * 100) : 0;
                                    $barColor = match ($value) {
                                        'ready' => 'bg-emerald-500',
                                        'draft' => 'bg-amber-500',
                                        'deprecated' => 'bg-slate-400',
                                        default => 'bg-indigo-500',
                                    };
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-sm text-gray-600">
                                        <span>{{ $label }}</span>
                                        <span>{{ $count }} · {{ $percentage }}%</span>
                                    </div>
                                    <div class="mt-2 h-2 rounded-full bg-gray-100 overflow-hidden">
                                        <div class="h-2 {{ $barColor }} transition-all duration-300" style="width: {{ $percentage }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </aside>

                <main class="space-y-6">
                    <section class="bg-white shadow rounded-xl p-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Filtrowanie i sortowanie</h3>
                                <p class="text-sm text-gray-500">Użyj filtrów, aby zawęzić listę przypadków</p>
                            </div>
                            <div class="text-right">
                                <a href="{{ route('test-cases.index') }}" class="text-sm text-gray-500 hover:text-emerald-600">Wyczyść filtry</a>
                            </div>
                        </div>
                        <form method="GET" class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div class="lg:col-span-2">
                                <x-input-label for="search" value="Słowa kluczowe" />
                                <x-text-input id="search" name="search" type="text" class="mt-1 block w-full"
                                              :value="request('search')" placeholder="ID, tytuł, oczekiwany rezultat..." />
                            </div>
                            <div>
                                <x-input-label for="folder_id" value="Folder" />
                                <select id="folder_id" name="folder_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    <option value="">Dowolny</option>
                                    @foreach($folders as $folder)
                                        <option value="{{ $folder->id }}" @selected((int) request('folder_id') === $folder->id)>
                                            {{ $folder->breadcrumb ?? $folder->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="status" value="Status" />
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    <option value="">Dowolny</option>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="sort" value="Sortowanie" />
                                <select id="sort" name="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    @foreach($sortOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2 lg:col-span-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($activeChips->isEmpty())
                                        <span class="text-xs font-semibold text-gray-400">Brak aktywnych filtrów</span>
                                    @else
                                        @foreach($activeChips as $label => $value)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                <span>{{ $label }}:</span>
                                                <span class="truncate max-w-[140px]">{{ $value }}</span>
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                                <button type="submit" class="inline-flex items-center justify-center px-6 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                    Zastosuj filtry
                                </button>
                            </div>
                        </form>
                    </section>

                    <section class="bg-white shadow rounded-xl overflow-hidden">
                        <div class="border-b border-gray-100 px-6 py-4 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Lista przypadków</p>
                                <p class="text-sm text-gray-500">
                                    {{ $testCases->total() }} wyników · Strona {{ $testCases->currentPage() }} z {{ $testCases->lastPage() }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 text-sm font-semibold">
                                <a href="{{ route('import-export.export') }}" class="text-emerald-600 hover:text-emerald-700">Eksportuj</a>
                                <a href="{{ route('import-export.index') }}" class="text-emerald-600 hover:text-emerald-700">Importuj</a>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-widest">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-widest">Tytuł i opis</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-widest">Folder</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-widest">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-widest">Aktualizacja</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-widest">Akcje</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($testCases as $case)
                                        <tr class="hover:bg-emerald-50/40">
                                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $case->case_key }}</td>
                                            <td class="px-6 py-4">
                                                <a href="{{ route('test-cases.show', $case) }}" class="text-base font-semibold text-gray-900 hover:text-emerald-600">
                                                    {{ $case->title }}
                                                </a>
                                                <p class="mt-1 text-sm text-gray-600">
                                                    {{ \Illuminate\Support\Str::limit($case->expected_result ?: $case->steps ?: 'Brak opisu', 110) }}
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
                        <div class="px-6 py-4 border-t border-gray-100">
                            {{ $testCases->links() }}
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
