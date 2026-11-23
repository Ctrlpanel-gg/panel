<?php

namespace App\View\Components;

use App\Facades\Captcha;
use Illuminate\View\Component;

class CaptchaWidget extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return '{!! \App\Facades\Captcha::renderWidget() !!}';
    }
}
