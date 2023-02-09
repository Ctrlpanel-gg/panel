<?php
/**
 * @required string $name
 * @required string $label
 *
 * @optional string $value
 */
?>

<div class="form-group mb-3">
    <div class="d-flex justify-content-between">
        <div {{ $attributes->merge(['class' => 'form-check form-switch']) }}>
            <input class="form-check-input" name="{{$name}}" value="1"
                   @if(old($name,  $value ?? null) == 1) checked @endif
                   type="checkbox"
                   id="{{$name}}">
            <label class="form-check-label" for="{{$name}}">{{$label}}</label>
        </div>

        @if(isset($tooltip) && !empty($tooltip))
            <span><i data-bs-toggle="tooltip" data-bs-placement="top" title="{{$tooltip}}"
                     class="fas fa-info-circle"></i></span>
        @endif
    </div>
</div>

@error($name)
<div class="invalid-feedback">{{$message}}</div>
@enderror


