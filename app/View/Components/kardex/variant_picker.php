<?php

namespace App\View\Components\kardex;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class variant_picker extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.kardex.variant_picker');
    }
}
