<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pola dodatkowe') }}
            </h2>
            <a href="{{ route('admin.custom-fields.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                Dodaj pole
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nazwa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Typ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kolejność</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($fields as $field)
                            <tr>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $field->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $field->field_type === 'textarea' ? 'Tekst wieloliniowy' : 'Tekst' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $field->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                        {{ $field->is_active ? 'Aktywne' : 'Wyłączone' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $field->position }}</td>
                                <td class="px-6 py-4 text-sm text-right space-x-3">
                                    <a href="{{ route('admin.custom-fields.edit', $field) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Edytuj</a>
                                    <form method="POST" action="{{ route('admin.custom-fields.destroy', $field) }}" class="inline" onsubmit="return confirm('Usunąć to pole?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold">Usuń</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-gray-500">Brak zdefiniowanych pól.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $fields->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
