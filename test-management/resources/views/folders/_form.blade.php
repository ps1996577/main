@props(['folder' => null, 'folders' => collect()])
<div class="grid gap-6">
    <div>
        <x-input-label for="name" value="Nazwa folderu" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $folder->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="description" value="Opis" />
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $folder->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="parent_id" value="Folder nadrzędny" />
            <select id="parent_id" name="parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">— brak —</option>
                @foreach($folders as $parent)
                    <option value="{{ $parent->id }}" @selected(old('parent_id', $folder->parent_id ?? '') == $parent->id)>
                        {{ $parent->breadcrumb ?? $parent->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="position" value="Kolejność" />
            <x-text-input id="position" name="position" type="number" min="0" class="mt-1 block w-full"
                          :value="old('position', $folder->position ?? 0)" />
            <x-input-error :messages="$errors->get('position')" class="mt-2" />
        </div>
    </div>
</div>
