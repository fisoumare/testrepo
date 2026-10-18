{{--
    Champ de formulaire accessible : libellé, contrôle (input, textarea ou select),
    aide facultative et message d'erreur relié par aria-describedby.
    Les attributs non déclarés (wire:model, autocomplete, placeholder, maxlength…)
    sont transmis au contrôle ; « class » s'applique au conteneur. « value » permet
    de rendre la valeur courante côté serveur (préremplissage avant l'initialisation de Livewire).
--}}
@props([
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'hint' => null,
    'options' => [],
    'rows' => 5,
    'id' => null,
    'value' => null,
])
@php
    $id = $id ?? 'champ-'.$name;
    $invalid = $errors->has($name);
    $describedBy = trim(($hint ? $id.'-aide' : '').' '.($invalid ? $id.'-erreur' : '')) ?: null;
    $control = 'block w-full rounded-md border bg-white px-3.5 py-2.5 text-[15px] text-navy-900 placeholder:text-ardoise/60 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/25 '
        .($invalid ? 'border-red-600' : 'border-navy-900/15 hover:border-navy-900/30');
    $controlAttributes = $attributes->except('class')->merge([
        'id' => $id,
        'name' => $name,
        'class' => $control,
        'aria-invalid' => $invalid ? 'true' : null,
        'aria-describedby' => $describedBy,
        'required' => $required ? true : null,
    ]);
@endphp
<div {{ $attributes->only('class')->merge(['class' => 'min-w-0']) }}>
    <label for="{{ $id }}" class="mb-1.5 flex items-baseline justify-between gap-3 text-sm font-medium text-navy-900">
        <span>{{ $label }}@if ($required) <span class="text-emerald-700" aria-hidden="true">*</span>@endif</span>
        @unless ($required)<span class="label text-ardoise">Facultatif</span>@endunless
    </label>

    @if ($type === 'textarea')
        <textarea {{ $controlAttributes->merge(['rows' => $rows]) }}>{{ $value }}</textarea>
    @elseif ($type === 'select')
        <select {{ $controlAttributes }}>
            <option value="">Choisir…</option>
            @foreach ($options as $optionValue => $text)
                <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $text }}</option>
            @endforeach
        </select>
    @else
        <input {{ $controlAttributes->merge(['type' => $type, 'value' => $value]) }}>
    @endif

    @if ($hint)
        <p id="{{ $id }}-aide" class="mt-1.5 text-xs leading-relaxed text-ardoise">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $id }}-erreur" class="mt-1.5 flex items-start gap-1.5 text-sm text-red-700" role="alert">
            <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M8 4.5v4M8 11v.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>
