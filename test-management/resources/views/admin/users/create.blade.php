<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Nowy użytkownik') }}
            </h2>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Powrót</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-xl p-6">
                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                    @csrf

                    @include('admin.users._form', ['user' => null, 'roles' => $roles])

                    <div class="flex items-center justify-end gap-3">
                        <x-secondary-button type="button" onclick="history.back()">Anuluj</x-secondary-button>
                        <x-primary-button>Zapisz</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
