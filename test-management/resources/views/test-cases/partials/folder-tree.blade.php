@foreach($nodes as $node)
    @php
        $isActive = (int) ($activeFolderId ?? 0) === $node->id;
        $query = array_merge($folderFilterQuery ?? [], ['folder_id' => $node->id]);
        $casePreview = $node->testCases->take(5);
    @endphp
    <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
        <button type="button" class="w-full flex items-center justify-between gap-2 text-left" @click="open = !open">
            <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-[0.3em] text-slate-400 truncate">{{ $node->breadcrumb }}</p>
                <p class="text-sm font-semibold text-slate-800 truncate">{{ $node->description ?? 'Bez opisu' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-500">{{ $node->test_cases_count }}</span>
                <i x-cloak x-show="!open" class="bi bi-caret-down text-slate-400"></i>
                <i x-cloak x-show="open" class="bi bi-caret-up text-slate-400"></i>
            </div>
        </button>
        <div x-show="open" x-transition.opacity class="pt-3 space-y-3">
            <div class="flex flex-wrap gap-2 text-[11px] font-semibold">
                <a href="{{ route('test-cases.index', $query) }}" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-emerald-700 hover:bg-emerald-100">
                    <i class="bi bi-filter"></i>
                    Filtruj
                </a>
                <a href="{{ route('folders.edit', $node) }}" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-slate-600 hover:text-emerald-600">
                    <i class="bi bi-pencil"></i>
                    Edytuj
                </a>
            </div>

            @if($casePreview->isNotEmpty())
                <ul class="space-y-2 text-xs text-slate-600">
                    @foreach($casePreview as $case)
                        <li class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('test-cases.show', $case) }}" class="font-semibold text-slate-800 hover:text-emerald-700 truncate">
                                    {{ $case->case_key }} · {{ $case->title }}
                                </a>
                                <p class="text-[11px] text-slate-500">
                                    {{ \Illuminate\Support\Str::limit($case->expected_result ?: $case->steps ?: 'Brak opisu', 60) }}
                                </p>
                            </div>
                            <x-status-badge :status="$case->status" class="px-2 py-0.5 text-[10px]" />
                        </li>
                    @endforeach
                </ul>
                @if($node->testCases->count() > $casePreview->count())
                    <p class="text-[11px] text-slate-500">+ {{ $node->testCases->count() - $casePreview->count() }} kolejnych...</p>
                @endif
            @else
                <p class="text-[11px] text-slate-400">Brak przypadków w tym folderze.</p>
            @endif

            @if($node->children->isNotEmpty())
                <div class="ms-3 border-l border-dashed border-slate-200 ps-3 space-y-2">
                    @include('test-cases.partials.folder-tree', [
                        'nodes' => $node->children,
                        'activeFolderId' => $activeFolderId,
                        'folderFilterQuery' => $folderFilterQuery,
                    ])
                </div>
            @endif
        </div>
    </div>
@endforeach
