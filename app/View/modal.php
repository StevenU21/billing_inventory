<?php

namespace App\View;

use Illuminate\View\Component;

class Modal extends Component
{
    public $title;
    public $description;

    public function __construct($title = '', $description = '')
    {
        $this->title = $title;
        $this->description = $description;
    }

    public function render()
    {
        return view('components.modal');
    }
}
