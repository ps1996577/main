@foreach($nodes as $node)
    <div class="border rounded-lg p-4 bg-white shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">{{ $node->breadcrumb }}</p>
                <p class="text-base font-semibold text-gray-900">{{ $node->description ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <span class="text-gray-500">{{ $node->testCases()->count() }} przypadków</span>
                <a href="{{ route('folders.edit', $node) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Edytuj</a>
                <form method="POST" action="{{ route('folders.destroy', $node) }}" onsubmit="return confirm('Usunąć folder wraz z zawartością?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold">Usuń</button>
                </form>
            </div>
        </div>
        @if($node->children->count())
            <div class="mt-4 ms-4 border-s-2 border-dashed border-gray-200 ps-4 space-y-3">
                @include('folders.partials.tree', ['nodes' => $node->children])
            </div>
        @endif
    </div>
@endforeach
