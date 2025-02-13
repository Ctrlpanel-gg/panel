<?php
/**
 * @required string $name
 * @required string $label
 *
 * @optional bool $multiple
 * @optional string $style
 * @optional string $tooltip
 */
?>
<div class="form-group mb-3">
    <div class="d-flex justify-content-between">
        <label for="{{$name}}">{{$label}}</label>
        @if(isset($tooltip) && !empty($tooltip))
            <span><i data-bs-toggle="tooltip" data-bs-placement="top" title="{{$tooltip}}" class="fas fa-info-circle"></i></span>
        @endif
    </div>

    <select
        class="form-control @if(isset($multiple) && $multiple) form-select-lg @endif  @error($name) is-invalid @enderror"
        @if(isset($multiple) && $multiple)multiple @endif
        @if(isset($style) && !empty($style))style="{{$style}}" @endif
        name="{{$name}}@if(isset($multiple) && $multiple)[]@endif"
        id="{{$name}}">

        {{$slot}}
    </select>

    @error($name)
        <div class="invalid-feedback">{{$message}}</div>
    @enderror
</div>
