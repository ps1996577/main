<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Struktura folderów') }}
            </h2>
            <a href="{{ route('folders.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Dodaj folder
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Hierarchia</h3>
                <div class="space-y-4">
                    @if($folders->count())
                        @include('folders.partials.tree', ['nodes' => $folders])
                    @else
                        <p class="text-gray-500">Brak folderów. Utwórz pierwszy folder, aby rozpocząć organizację przypadków testowych.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
