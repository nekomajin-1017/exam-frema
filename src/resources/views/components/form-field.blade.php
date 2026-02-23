@props([
    'name',
    'label' => '',
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'useOld' => true,
])

@php
    $fieldValue = $useOld ? old($name, $value) : $value;
    $errorBag = $errors ?? null;
    $errorMessage = $errorBag ? $errorBag->first($name) : null;
@endphp

<div class="form-group">
    @if($label !== '')
        <label class="form-label" for="{{ $name }}">{{ $label }}</label>
    @endif

    <input
        id="{{ $name }}"
        class="form-control"
        name="{{ $name }}"
        type="{{ $type }}"
        placeholder="{{ $placeholder }}"
        @if(! is_null($fieldValue) && $type !== 'password')
            value="{{ $fieldValue }}"
        @endif
        {{ $attributes }}
    >

    @if($errorMessage)
        <p class="field-error">{{ $errorMessage }}</p>
    @endif
</div>
