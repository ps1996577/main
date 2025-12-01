<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <x-symfonia-logo class="flex" />
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

    <div class="py-10 bg-slate-50">
        <div class="container-xxl px-3">
            <div class="row g-4">
                <div class="col-lg-9 d-flex flex-column gap-4">
                    <div class="card border-0 shadow-sm glass-card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100 bg-white/80">
                                        <p class="text-xs text-slate-500 mb-1">Gotowe</p>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="badge bg-emerald-100 text-emerald-700 fs-6">{{ $metrics['ready'] }}</span>
                                            <span class="text-slate-600 text-sm">przypadków gotowych do testów</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100 bg-white/80">
                                        <p class="text-xs text-slate-500 mb-1">Szkice</p>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="badge bg-warning-subtle text-warning fs-6">{{ $metrics['draft'] }}</span>
                                            <span class="text-slate-600 text-sm">w przygotowaniu</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100 bg-white/80">
                                        <p class="text-xs text-slate-500 mb-1">Wycofane</p>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="badge bg-secondary-subtle text-secondary fs-6">{{ $metrics['deprecated'] }}</span>
                                            <span class="text-slate-600 text-sm">poza użyciem</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm glass-card">
                        <div class="card-header bg-transparent border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h5 class="mb-0 text-slate-800">Filtry i sortowanie</h5>
                                <small class="text-slate-500">Zawęż listę zgodnie z potrzebą zespołu QA</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('test-cases.index') }}" class="btn btn-outline-secondary btn-sm">Wyczyść</a>
                                <button form="filters-form" class="btn btn-success btn-sm d-flex align-items-center gap-2">
                                    <i class="bi bi-funnel"></i> Zastosuj
                                </button>
                            </div>
                        </div>
                        <div class="card-body border-top">
                            <form id="filters-form" method="GET" class="row gy-4">
                                <div class="col-lg-5">
                                    <x-input-label for="search" value="Słowa kluczowe" />
                                    <x-text-input id="search" name="search" type="text" class="mt-1 block w-full"
                                                  :value="request('search')" placeholder="ID, tytuł, oczekiwany rezultat..." />
                                </div>
                                <div class="col-lg-3">
                                    <x-input-label for="folder_id" value="Folder" />
                                    <select id="folder_id" name="folder_id" class="form-select mt-1">
                                        <option value="">Dowolny</option>
                                        @foreach($folders as $folder)
                                            <option value="{{ $folder->id }}" @selected((int) request('folder_id') === $folder->id)>
                                                {{ $folder->breadcrumb ?? $folder->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <x-input-label for="status" value="Status" />
                                    <select id="status" name="status" class="form-select mt-1">
                                        <option value="">Dowolny</option>
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <x-input-label for="sort" value="Sortowanie" />
                                    <select id="sort" name="sort" class="form-select mt-1">
                                        @foreach($sortOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 d-flex flex-wrap gap-2 align-items-center">
                                    @if($activeChips->isEmpty())
                                        <span class="badge bg-light text-muted">Brak aktywnych filtrów</span>
                                    @else
                                        @foreach($activeChips as $label => $value)
                                            <span class="badge bg-success-subtle text-success d-flex align-items-center gap-1">
                                                <i class="bi bi-funnel-fill"></i> {{ $label }}: {{ $value }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm glass-card">
                        <div class="card-header bg-transparent border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h5 class="mb-0 text-slate-800">Lista przypadków</h5>
                                <small class="text-slate-500">{{ $testCases->total() }} wyników · Strona {{ $testCases->currentPage() }} z {{ $testCases->lastPage() }}</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                                    <i class="bi bi-copy"></i> Duplikuj
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-2">
                                    <i class="bi bi-trash"></i> Usuń zaznaczone
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Tytuł</th>
                                        <th scope="col">Folder</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Aktualizacja</th>
                                        <th scope="col" class="text-end">Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($testCases as $case)
                                        <tr class="align-top">
                                            <td class="fw-semibold text-slate-900">{{ $case->case_key }}</td>
                                            <td>
                                                <a href="{{ route('test-cases.show', $case) }}" class="fw-semibold text-decoration-none text-slate-900 hover:text-emerald-600">
                                                    {{ $case->title }}
                                                </a>
                                                <p class="text-sm text-slate-600 mb-0">
                                                    {{ \Illuminate\Support\Str::limit($case->expected_result ?: $case->steps ?: 'Brak opisu', 120) }}
                                                </p>
                                            </td>
                                            <td class="text-sm text-slate-600">
                                                {{ optional($case->folder)->breadcrumb ?? 'Brak folderu' }}
                                            </td>
                                            <td><x-status-badge :status="$case->status" /></td>
                                            <td class="text-sm text-slate-500">{{ $case->updated_at?->diffForHumans() }}</td>
                                            <td class="text-end">
                                                <div class="d-flex flex-column gap-1 align-items-end">
                                                    <a href="{{ route('test-cases.show', $case) }}" class="text-success fw-semibold text-decoration-none">
                                                        <i class="bi bi-eye"></i> Podgląd
                                                    </a>
                                                    <a href="{{ route('test-cases.edit', $case) }}" class="text-primary fw-semibold text-decoration-none">
                                                        <i class="bi bi-pencil"></i> Edytuj
                                                    </a>
                                                    <form method="POST" action="{{ route('test-cases.destroy', $case) }}" class="d-inline" onsubmit="return confirm('Usunąć ten przypadek?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-link p-0 text-danger fw-semibold">
                                                            <i class="bi bi-x-circle"></i> Usuń
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-slate-500 py-4">Brak przypadków spełniających kryteria.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            {{ $testCases->links() }}
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm glass-card sticky top-24">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400 mb-0">Foldery</p>
                                    <h5 class="mb-0 text-slate-900">Struktura przypadków</h5>
                                </div>
                                <span class="badge bg-light text-muted">{{ $metrics['total'] }} TC</span>
                            </div>
                            <a href="{{ route('test-cases.index', $folderFilterQuery) }}" class="d-inline-flex align-items-center gap-2 text-xs text-slate-500 mb-3 text-decoration-none">
                                <i class="bi bi-grid"></i> Wszystkie foldery
                            </a>
                            @if($folderTree->isNotEmpty())
                                <div class="d-flex flex-column gap-3 custom-scroll" style="max-height: 540px; overflow-y: auto;">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
