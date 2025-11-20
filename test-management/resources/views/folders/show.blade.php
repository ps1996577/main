<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500">{{ $folder->breadcrumb }}</p>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    {{ $folder->name }}
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('folders.edit', $folder) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                    Edytuj
                </a>
                <a href="{{ route('folders.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Wróć do drzewa</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Opis</h3>
                <p class="text-gray-700">{{ $folder->description ?: 'Brak opisu.' }}</p>
            </div>

            <div class="bg-white shadow rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Przypadki w folderze</h3>
                    <a href="{{ route('test-cases.create', ['folder_id' => $folder->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">Dodaj nowy</a>
                </div>
                <div class="space-y-3">
                    @forelse($folder->testCases as $case)
                        <div class="flex items-center justify-between border rounded-lg px-4 py-3">
                            <div>
                                <p class="text-sm text-gray-500">{{ $case->case_key }}</p>
                                <a href="{{ route('test-cases.show', $case) }}" class="text-base font-semibold text-gray-900 hover:text-indigo-600">
                                    {{ $case->title }}
                                </a>
                            </div>
                            <x-status-badge :status="$case->status" />
                        </div>
                    @empty
                        <p class="text-gray-500">Brak przypadków w tym folderze.</p>
                    @endforelse
                </div>
            </div>

            @if($folder->children->count())
                <div class="bg-white shadow rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Podfoldery</h3>
                    <div class="space-y-3">
                        @foreach($folder->children as $child)
                            <div class="flex items-center justify-between border rounded-lg px-4 py-3">
                                <div>
                                    <p class="text-sm text-gray-500">{{ $child->breadcrumb }}</p>
                                    <p class="text-base font-semibold text-gray-900">{{ $child->description ?? '—' }}</p>
                                </div>
                                <a href="{{ route('folders.show', $child) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Podgląd</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
