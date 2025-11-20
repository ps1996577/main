@props(['user' => null, 'roles' => []])
<div class="grid gap-6">
    <div>
        <x-input-label for="name" value="Imię i nazwisko" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $user->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="email" value="E-mail" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                      :value="old('email', $user->email ?? '')" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="role" value="Rola" />
            <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach($roles as $value => $label)
                    <option value="{{ $value }}" @selected(old('role', $user->role ?? 'tester') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="password" value="{{ $user ? 'Nowe hasło (opcjonalnie)' : 'Hasło' }}" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" @if(!$user) required @endif />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
    </div>
    <div>
        <x-input-label for="password_confirmation" value="Powtórz hasło" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" @if(!$user) required @endif />
    </div>
</div>
