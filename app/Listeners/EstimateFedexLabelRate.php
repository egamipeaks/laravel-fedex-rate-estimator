<?php

namespace App\Listeners;

use App\Events\FedexLabelCreatedOrUpdatedEvent;
use App\FedEx\Services\EstimateRateForLabelService;
use Illuminate\Queue\InteractsWithQueue;

class EstimateFedexLabelRate
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FedexLabelCreatedOrUpdatedEvent $event): void
    {
        app(EstimateRateForLabelService::class)->run($event->label);
    }
}
