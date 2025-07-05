<?php

namespace App\FedEx\Services;

use ShipStream\FedEx\Api\RatesAndTransitTimesV1\Api;
use ShipStream\FedEx\Api\RatesAndTransitTimesV1\Dto\AccountNumber;
use ShipStream\FedEx\Api\RatesAndTransitTimesV1\Dto\Address;
use ShipStream\FedEx\Api\RatesAndTransitTimesV1\Dto\Dimensions;
use ShipStream\FedEx\Api\RatesAndTransitTimesV1\Dto\FullSchemaQuoteRate;
use ShipStream\FedEx\Api\RatesAndTransitTimesV1\Dto\RateParty;
use ShipStream\FedEx\Api\RatesAndTransitTimesV1\Dto\RequestedPackageLineItem;
use ShipStream\FedEx\Api\RatesAndTransitTimesV1\Dto\RequestedShipment;
use ShipStream\FedEx\Api\RatesAndTransitTimesV1\Dto\Weight;

class FedExRateService
{
    protected Api $connector;

    public function __construct(FedExRatesConnector $fedex)
    {
        $this->connector = $fedex->ratesTransitTimesV1();
    }

    public function estimate(array $origin, array $destination, array $packageDetails): array
    {
        $originAddress = new Address(
            city: $origin['city'] ?? null,
            stateOrProvinceCode: $origin['stateOrProvinceCode'] ?? null,
            postalCode: $origin['postal_code'] ?? null,
            countryCode: 'US',
            residential: $origin['residential'] ?? false,
        );

        $isResidential = $destination['residential'] ?? false;
        $serviceType = $isResidential ? 'GROUND_HOME_DELIVERY' : 'FEDEX_GROUND';

        $destinationAddress = new Address(
            city: $destination['city'] ?? null,
            stateOrProvinceCode: $destination['stateOrProvinceCode'] ?? null,
            postalCode: $destination['postal_code'] ?? null,
            countryCode: 'US',
            residential: $destination['residential'] ?? false,
        );

        $weight = new Weight(
            units: $packageDetails['weight_unit'] ?? 'LB',
            value: $packageDetails['weight'] ?? null,
        );

        $dimensions = null;

        if (! empty($packageDetails['length']) && ! empty($packageDetails['width']) &&
             ! empty($packageDetails['height'])) {
            $dimensions = new Dimensions(
                length: $packageDetails['length'],
                width: $packageDetails['width'],
                height: $packageDetails['height'],
                units: $packageDetails['dimensions_unit'] ?? 'IN',
            );
        }

        $fullSchemaQuoteRate = new FullSchemaQuoteRate(
            new AccountNumber(config('services.fedex.account_number')),
            new RequestedShipment(
                shipper: new RateParty($originAddress),
                recipient: new RateParty($destinationAddress),
                pickupType: 'DROPOFF_AT_FEDEX_LOCATION',
                requestedPackageLineItems: [
                    new RequestedPackageLineItem(
                        weight: $weight,
                        dimensions: $dimensions
                    ),
                ],
                serviceType: $serviceType,
                rateRequestType: [
                    'ACCOUNT',
                    'LIST',
                ],
            )
        );

        $response = $this->connector->rateAndTransitTimes($fullSchemaQuoteRate);

        return $response->json();
    }
}
