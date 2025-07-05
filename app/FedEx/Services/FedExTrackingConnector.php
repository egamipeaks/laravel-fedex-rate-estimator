<?php

namespace App\FedEx\Services;

use ShipStream\FedEx\Enums\Endpoint;
use ShipStream\FedEx\FedEx;

class FedExTrackingConnector extends FedEx
{
    public function __construct()
    {
        parent::__construct(
            clientId: config('services.fedex.tracking.client_id'),
            clientSecret: config('services.fedex.tracking.client_secret'),
            endpoint: config('services.fedex.mode') === 'sandbox'
                ? Endpoint::SANDBOX
                : Endpoint::PROD,
        );
    }
}
