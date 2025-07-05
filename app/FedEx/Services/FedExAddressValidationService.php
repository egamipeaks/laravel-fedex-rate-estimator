<?php

namespace App\FedEx\Services;

use ShipStream\FedEx\Api\AddressValidationV1\Api;
use ShipStream\FedEx\Api\AddressValidationV1\Dto\Address;
use ShipStream\FedEx\Api\AddressValidationV1\Dto\FullSchemaValidateAddress;
use ShipStream\FedEx\Api\AddressValidationV1\Dto\ResolveContactAndAddress;

class FedExAddressValidationService
{
    protected Api $connector;

    public function __construct(FedExRatesConnector $fedex)
    {
        $this->connector = $fedex->addressValidationV1();
    }

    public function validateAddresses(array $addresses = []): array
    {
        $resolvedAddresses = collect($addresses)
            ->map(function ($address) {
                $streetLines = $address['streetLines'] ?? [];

                if (is_string($streetLines)) {
                    $streetLines = [$streetLines];
                }

                $city = $address['city'] ?? '';
                $stateOrProvinceCode = $address['stateOrProvinceCode'] ?? '';
                $postalCode = $address['postalCode'] ?? '';

                if (empty($streetLines) || empty($city) || empty($stateOrProvinceCode) || empty($postalCode)) {
                    throw new \InvalidArgumentException('Invalid address data provided.');
                }

                return new ResolveContactAndAddress(
                    new Address(
                        streetLines: $streetLines,
                        city: $city,
                        postalCode: $postalCode,
                        stateOrProvinceCode: $stateOrProvinceCode,
                        countryCode: 'US'
                    )
                );
            })
            ->all();

        $response = $this->connector->validateAddress(
            new FullSchemaValidateAddress($resolvedAddresses)
        );

        return $response->json();
    }
}
