<?php
namespace App\View\Components;

use Illuminate\View\Component;

class TableSortHeader extends Component
{
    public $field;
    public $label;
    public $icon;

    public function __construct($field, $label, $icon)
    {
        $this->field = $field;
        $this->label = $label;
        $this->icon = $icon;
    }

    public function render()
    {
        return view('components.table-sort-header');
    }
}
