<?php
/**
 * @required string $name
 * @required string $label
 * @required string $accept
 *
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


        <div class="input-group">
            <input value="{{old($name,  $value ?? null)}}" id="{{$name}}"
                   name="{{$name}}"
                   type="file" class="form-control @error($name)is-invalid @enderror"
                    accept="{{$accept}}" />
        </div>


    @error($name)
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
</div>


