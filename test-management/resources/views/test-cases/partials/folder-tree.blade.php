@foreach($nodes as $node)
    @php
        $isActive = (int) ($activeFolderId ?? 0) === $node->id;
        $query = array_merge($folderFilterQuery ?? [], ['folder_id' => $node->id]);
        $casePreview = $node->testCases->take(6);
    @endphp
    <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="rounded-2xl border border-white/50 bg-white/40 backdrop-blur p-3">
        <button type="button" class="w-full flex items-center justify-between gap-3 text-left" @click="open = !open">
            <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-[0.3em] text-gray-400 truncate">{{ $node->breadcrumb }}</p>
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $node->description ?? 'Bez opisu' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-gray-500">{{ $node->test_cases_count }}</span>
                <svg x-cloak x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                <svg x-cloak x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
        </button>
        <div x-show="open" x-transition.opacity class="pt-3 space-y-3">
            <div class="flex flex-wrap gap-2 text-[11px] font-semibold">
                <a href="{{ route('test-cases.index', $query) }}" class="inline-flex items-center gap-1 rounded-full bg-emerald-50/80 px-3 py-1 text-emerald-700 hover:bg-emerald-100">
                    Filtruj folder
                </a>
                <a href="{{ route('folders.edit', $node) }}" class="inline-flex items-center gap-1 rounded-full bg-white/70 px-3 py-1 text-gray-600 hover:text-emerald-600">
                    Edytuj folder
                </a>
            </div>

            @if($casePreview->isNotEmpty())
                <ul class="space-y-2 text-xs text-gray-600">
                    @foreach($casePreview as $case)
                        <li class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('test-cases.show', $case) }}" class="font-semibold text-gray-800 hover:text-emerald-700 truncate">
                                    {{ $case->case_key }} · {{ $case->title }}
                                </a>
                                <p class="text-[11px] text-gray-500">
                                    {{ \Illuminate\Support\Str::limit($case->expected_result ?: $case->steps ?: 'Brak opisu', 60) }}
                                </p>
                            </div>
                            <x-status-badge :status="$case->status" class="px-2 py-0.5 text-[10px]" />
                        </li>
                    @endforeach
                </ul>
                @if($node->testCases->count() > $casePreview->count())
                    <p class="text-[11px] text-gray-500">+ {{ $node->testCases->count() - $casePreview->count() }} kolejnych...</p>
                @endif
            @else
                <p class="text-[11px] text-gray-400">Brak przypadków w tym folderze.</p>
            @endif

            @if($node->children->isNotEmpty())
                <div class="ms-3 border-l border-dashed border-white/60 ps-3 space-y-2">
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
