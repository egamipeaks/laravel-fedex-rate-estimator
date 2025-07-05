<?php

namespace App\View\Components\Email\Fedex;

use App\Models\FedexLabel;
use Illuminate\View\Component;

class LabelRow extends Component
{
    public ?array $priceBreakdown = null;

    public ?string $serviceType = null;

    public function __construct(public FedexLabel $label)
    {
        $this->serviceType = $label->latestEstimate?->service_type;
        $this->priceBreakdown = $label->latestEstimate?->getFormattedPriceBreakdown() ?: null;
    }

    public function render()
    {
        return view('components.email.fedex.label-row');
    }
}
