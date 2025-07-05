<?php

namespace App\Events;

use App\Models\FedexLabel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FedexLabelCreatedOrUpdatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public FedexLabel $label,
        public bool $isNew = false
    ) {}
}
