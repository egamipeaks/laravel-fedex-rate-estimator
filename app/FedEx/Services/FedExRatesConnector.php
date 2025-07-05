<?php

namespace App\FedEx\Services;

use ShipStream\FedEx\Enums\Endpoint;
use ShipStream\FedEx\FedEx;

class FedExRatesConnector extends FedEx
{
    public function __construct()
    {
        parent::__construct(
            clientId: config('services.fedex.rates.client_id'),
            clientSecret: config('services.fedex.rates.client_secret'),
            endpoint: config('services.fedex.mode') === 'sandbox'
                ? Endpoint::SANDBOX
                : Endpoint::PROD,
        );
    }
}
