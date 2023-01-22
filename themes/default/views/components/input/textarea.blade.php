<?php
/**
 * @required string $name
 * @required string $label
 *
 * @optional string $value
 * @optional string $rows
 * @optional string $tooltip
 * @optional bool $ckeditor
 */
?>

<div class="form-group mb-3">
    <div class="d-flex justify-content-between">
        <label for="{{$name}}">{{$label}}</label>
        @if(isset($tooltip) && !empty($tooltip))
            <span><i data-bs-toggle="tooltip" data-bs-placement="top" title="{{$tooltip}}" class="fas fa-info-circle"></i></span>
        @endif
    </div>

    <textarea rows="{{$rows ?? 2}}" id="{{$name}}" name="{{$name}}" class="form-control @isset($ckeditor) ckeditor @endif @error($name)is-invalid @enderror">{{old($name,  $value ?? null)}}</textarea>

    @error($name)
        <div class="invalid-feedback">{{$message}}</div>
    @enderror
</div>
