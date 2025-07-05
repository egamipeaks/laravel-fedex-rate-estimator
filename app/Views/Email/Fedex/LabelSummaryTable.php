<?php

namespace App\View\Components\Email\Fedex;

use Illuminate\Support\Collection;
use Illuminate\View\Component;

class LabelSummaryTable extends Component
{
    public function __construct(public Collection $labels) {}

    public function render()
    {
        return view('components.email.fedex.label-summary-table');
    }
}
