@props(['testCase' => null, 'folders' => collect(), 'customFields' => collect()])
@php
    $isEdit = $testCase?->exists ?? false;
@endphp
<div class="grid gap-6">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="case_key" value="ID przypadku (opcjonalnie)" />
            <x-text-input id="case_key" name="case_key" type="text" class="mt-1 block w-full"
                          :value="old('case_key', $testCase->case_key ?? '')" />
            <x-input-error :messages="$errors->get('case_key')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="folder_id" value="Folder" />
            <select id="folder_id" name="folder_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">— Brak —</option>
                @foreach($folders as $folder)
                    <option value="{{ $folder->id }}" @selected(old('folder_id', $testCase->folder_id ?? '') == $folder->id)>
                        {{ $folder->breadcrumb ?? $folder->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('folder_id')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="title" value="Cel testu" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                      :value="old('title', $testCase->title ?? '')" required />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach(['draft' => 'Szkic', 'ready' => 'Gotowy', 'deprecated' => 'Wycofany'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $testCase->status ?? 'draft') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="preconditions" value="Wymagania wstępne" />
        <textarea id="preconditions" name="preconditions" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('preconditions', $testCase->preconditions ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('preconditions')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="steps" value="Kroki testowe" />
        <textarea id="steps" name="steps" rows="5"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('steps', $testCase->steps ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('steps')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="expected_result" value="Oczekiwany rezultat" />
        <textarea id="expected_result" name="expected_result" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('expected_result', $testCase->expected_result ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('expected_result')" class="mt-2" />
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="acceptance_criteria" value="Kryteria zaliczenia" />
            <textarea id="acceptance_criteria" name="acceptance_criteria" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('acceptance_criteria', $testCase->acceptance_criteria ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('acceptance_criteria')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="additional_notes" value="Uwagi dodatkowe" />
            <textarea id="additional_notes" name="additional_notes" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('additional_notes', $testCase->additional_notes ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('additional_notes')" class="mt-2" />
        </div>
    </div>

    @if($customFields->count())
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Pola niestandardowe</h3>
            <div class="grid gap-4">
                @foreach($customFields as $field)
                    <div>
                        <x-input-label :for="'custom-field-' . $field->id" :value="$field->name . ($field->is_required ? ' *' : '')" />
                        @if($field->field_type === 'textarea')
                            <textarea id="custom-field-{{ $field->id }}"
                                      name="custom_fields[{{ $field->id }}]" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      @if($field->is_required) required @endif>{{ old('custom_fields.' . $field->id, $testCase?->getCustomFieldValue($field->id)) }}</textarea>
                        @else
                            <x-text-input id="custom-field-{{ $field->id }}"
                                          name="custom_fields[{{ $field->id }}]"
                                          type="text"
                                          class="mt-1 block w-full"
                                          :value="old('custom_fields.' . $field->id, $testCase?->getCustomFieldValue($field->id))"
                                          @if($field->is_required) required @endif />
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
