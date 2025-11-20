<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Przypadki testowe') }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('test-cases.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Dodaj przypadek
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white shadow rounded-xl p-6">
                <form method="GET" class="grid gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <x-input-label for="search" value="Szukaj" />
                        <x-text-input id="search" name="search" type="text" class="mt-1 block w-full"
                                      :value="request('search')" placeholder="ID, tytuł, oczekiwany rezultat..." />
                    </div>
                    <div>
                        <x-input-label for="folder_id" value="Folder" />
                        <select id="folder_id" name="folder_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Wszystkie</option>
                            @foreach($folders as $folder)
                                <option value="{{ $folder->id }}" @selected(request('folder_id') == $folder->id)>
                                    {{ $folder->breadcrumb ?? $folder->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Wszystkie</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4 flex items-center gap-3 justify-end">
                        <a href="{{ route('test-cases.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Wyczyść</a>
                        <x-primary-button>Filtruj</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cel testu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folder</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktualizacja</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($testCases as $case)
                            <tr>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $case->case_key }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <a href="{{ route('test-cases.show', $case) }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                                        {{ $case->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ optional($case->folder)->breadcrumb ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <x-status-badge :status="$case->status" />
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $case->updated_at?->diffForHumans() }}</td>
                                <td class="px-6 py-4 text-sm text-right space-x-2">
                                    <a href="{{ route('test-cases.edit', $case) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Edytuj</a>
                                    <form method="POST" action="{{ route('test-cases.destroy', $case) }}" class="inline" onsubmit="return confirm('Usunąć ten przypadek?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold">Usuń</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-6 text-center text-gray-500">Brak przypadków spełniających kryteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $testCases->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
