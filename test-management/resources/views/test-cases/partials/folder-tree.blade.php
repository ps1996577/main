@foreach($nodes as $node)
    @php
        $isActive = (int) ($activeFolderId ?? 0) === $node->id;
        $query = request()->except('page');
        $query['folder_id'] = $node->id;
        $url = route('test-cases.index', $query);
    @endphp
    <div>
        <a href="{{ $url }}"
           class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm {{ $isActive ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
            <span class="truncate">{{ $node->breadcrumb }}</span>
            <span class="text-xs font-semibold text-gray-500">{{ $node->test_cases_count }}</span>
        </a>
        @if($node->children->isNotEmpty())
            <div class="ms-3 mt-2 border-l border-dashed border-gray-200 ps-3 space-y-2">
                @include('test-cases.partials.folder-tree', [
                    'nodes' => $node->children,
                    'activeFolderId' => $activeFolderId,
                ])
            </div>
        @endif
    </div>
@endforeach
