@props(['customField' => null])
<div class="grid gap-6">
    <div>
        <x-input-label for="name" value="Nazwa pola" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $customField->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <x-input-label for="slug" value="Identyfikator" />
            <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full"
                          :value="old('slug', $customField->slug ?? '')" placeholder="np. priorytet" />
            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="field_type" value="Typ pola" />
            <select id="field_type" name="field_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach(['text' => 'Tekst jednoliniowy', 'textarea' => 'Tekst wieloliniowy'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('field_type', $customField->field_type ?? 'text') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('field_type')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="position" value="Kolejność" />
            <x-text-input id="position" name="position" type="number" min="0" class="mt-1 block w-full"
                          :value="old('position', $customField->position ?? 0)" />
            <x-input-error :messages="$errors->get('position')" class="mt-2" />
        </div>
    </div>
    <div class="flex items-center gap-6">
        <label class="inline-flex items-center">
            <input type="checkbox" name="is_required" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                   @checked(old('is_required', $customField->is_required ?? false))>
            <span class="ms-2 text-gray-700">Pole wymagane</span>
        </label>
        <label class="inline-flex items-center">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                   @checked(old('is_active', $customField->is_active ?? true))>
            <span class="ms-2 text-gray-700">Aktywne</span>
        </label>
    </div>
</div>
