<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500">Zarządzaj strukturą przypadków testowych</p>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    {{ __('Przypadki testowe') }}
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('test-cases.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
        ])->filter();
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-dashboard-card label="Widoczne przypadki" :value="$metrics['total']" icon="stack" />
                <x-dashboard-card label="Gotowe" :value="$metrics['ready']" tone="success" icon="check" />
                <x-dashboard-card label="Szkice" :value="$metrics['draft']" tone="warning" icon="pencil" />
                <x-dashboard-card label="Wycofane" :value="$metrics['deprecated']" tone="danger" icon="archive" />
            </div>

            <div class="grid gap-6 lg:grid-cols-[320px,1fr]">
                <aside class="space-y-6">
                    <section class="bg-white shadow rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Filtry</h3>
                            <a href="{{ route('test-cases.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Wyczyść</a>
                        </div>
                        <form method="GET" id="test-cases-filter" class="mt-4 space-y-4">
                            <div>
                                <x-input-label for="search" value="Słowa kluczowe" />
                                <x-text-input id="search" name="search" type="text" class="mt-1 block w-full"
                                              :value="request('search')" placeholder="ID, tytuł, rezultat..." />
                            </div>
                            <div>
                                <x-input-label for="folder_id" value="Folder" />
                                <select id="folder_id" name="folder_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">Wszystkie foldery</option>
                                    @foreach($folders as $folder)
                                        <option value="{{ $folder->id }}" @selected((int) request('folder_id') === $folder->id)>
                                            {{ $folder->breadcrumb ?? $folder->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="status" value="Status" />
                                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">Dowolny</option>
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="sort" value="Sortowanie" />
                                    <select id="sort" name="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        @foreach($sortOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <x-primary-button class="w-full justify-center">Zastosuj filtry</x-primary-button>
                        </form>
                    </section>

                    <section class="bg-white shadow rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Statusy</h3>
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
                                        <div class="h-2 {{ $barColor }}" style="width: {{ $percentage }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="bg-white shadow rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Foldery</h3>
                            <a href="{{ route('folders.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Zarządzaj</a>
                        </div>
                        <div class="mt-4 space-y-3 text-sm text-gray-600">
                            @php
                                $allFoldersQuery = request()->except(['page', 'folder_id']);
                            @endphp
                            <a href="{{ route('test-cases.index', $allFoldersQuery) }}"
                               class="inline-flex items-center gap-2 text-xs font-medium text-gray-500 hover:text-indigo-600">
                                <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                Wszystkie foldery
                            </a>
                            @if($folderTree->isNotEmpty())
                                <div class="space-y-2">
                                    @include('test-cases.partials.folder-tree', [
                                        'nodes' => $folderTree,
                                        'activeFolderId' => request('folder_id'),
                                    ])
                                </div>
                            @else
                                <p class="text-gray-500">Nie zdefiniowano folderów.</p>
                            @endif
                        </div>
                    </section>
                </aside>

                <main class="space-y-6">
                    <section class="bg-white shadow rounded-xl">
                        <div class="border-b border-gray-100 px-6 py-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Lista przypadków</p>
                                <p class="text-sm text-gray-500">
                                    {{ $testCases->total() }} wyników · Strona {{ $testCases->currentPage() }} z {{ $testCases->lastPage() }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if($activeChips->isEmpty())
                                    <span class="text-xs font-medium text-gray-500">Brak aktywnych filtrów</span>
                                @else
                                    @foreach($activeChips as $label => $value)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                            <span>{{ $label }}:</span>
                                            <span class="truncate">{{ $value }}</span>
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            @forelse($testCases as $case)
                                <li class="px-6 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-widest text-gray-400">
                                            <span>{{ $case->case_key }}</span>
                                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                            <span class="text-gray-500">{{ optional($case->folder)->breadcrumb ?? 'Brak folderu' }}</span>
                                        </div>
                                        <a href="{{ route('test-cases.show', $case) }}" class="mt-1 text-lg font-semibold text-gray-900 hover:text-indigo-600">
                                            {{ $case->title }}
                                        </a>
                                        <p class="mt-2 text-sm text-gray-600">
                                            {{ \Illuminate\Support\Str::limit($case->expected_result ?: ($case->steps ?? 'Brak opisu'), 140) }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-start gap-2 md:items-end">
                                        <x-status-badge :status="$case->status" />
                                        <p class="text-xs text-gray-500">Aktualizacja {{ $case->updated_at?->diffForHumans() }}</p>
                                        <div class="flex items-center gap-3 text-sm font-semibold">
                                            <a href="{{ route('test-cases.show', $case) }}" class="text-indigo-600 hover:text-indigo-800">Podgląd</a>
                                            <a href="{{ route('test-cases.edit', $case) }}" class="text-gray-600 hover:text-gray-800">Edytuj</a>
                                            <form method="POST" action="{{ route('test-cases.destroy', $case) }}" onsubmit="return confirm('Usunąć ten przypadek?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800">Usuń</button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="px-6 py-8 text-center text-gray-500">
                                    Brak przypadków spełniających kryteria.
                                </li>
                            @endforelse
                        </ul>
                        <div class="px-6 py-4 border-t border-gray-100">
                            {{ $testCases->links() }}
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
