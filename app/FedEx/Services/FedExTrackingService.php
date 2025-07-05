<?php

namespace App\FedEx\Services;

use ShipStream\FedEx\Api\TrackV1\Api;
use ShipStream\FedEx\Api\TrackV1\Dto\FullSchemaTrackingNumbers;
use ShipStream\FedEx\Api\TrackV1\Dto\FullSchemaTrackingReferences;
use ShipStream\FedEx\Api\TrackV1\Dto\ReferenceInformation;
use ShipStream\FedEx\Api\TrackV1\Dto\TrackingInfo;
use ShipStream\FedEx\Api\TrackV1\Dto\TrackingNumberInfo;

class FedExTrackingService
{
    protected Api $connector;

    public function __construct(FedExTrackingConnector $fedex)
    {
        $this->connector = $fedex->trackV1();
    }

    public function track(string $trackingNumber): array
    {
        $fullSchemaTrackingNumbers = new FullSchemaTrackingNumbers(true, [
            new TrackingInfo(new TrackingNumberInfo($trackingNumber)),
        ]);

        $response = $this->connector->trackByTrackingNumber($fullSchemaTrackingNumbers);

        return $response->json();
    }

    public function trackMultiple(array $trackingNumbers): array
    {
        $trackingInfoList = collect($trackingNumbers)
            ->map(fn ($tn) => new TrackingInfo(new TrackingNumberInfo($tn)))
            ->all();

        $request = new FullSchemaTrackingNumbers(true, $trackingInfoList);

        $response = $this->connector->trackByTrackingNumber($request);

        return $response->json();
    }

    public function trackByPurchaseOrder(string $purchaseOrder): array
    {
        $response = $this->connector->trackByReferences(
            new FullSchemaTrackingReferences(
                new ReferenceInformation(
                    value: $purchaseOrder,
                    carrierCode: 'FDXE',
                    type: 'CUSTOMER_REFERENCE',
                    accountNumber: config('services.fedex.account_number'),
                    destinationCountryCode: 'US',
                )
            )
        );

        return $response->json();
    }
}
