<?php

namespace Tests\Models;

use App\Models\FedexLabelEstimate;

test('it returns an empty array when raw payload is not set', function () {
    $estimate = new FedexLabelEstimate;

    $result = $estimate->getFormattedPriceBreakdown();

    expect($result)->toBe([]);
});

test('it returns an empty array when rated shipment details are not set', function () {
    $estimate = new FedexLabelEstimate([
        'raw_payload' => [
            'someOtherKey' => 'value',
        ],
    ]);

    $result = $estimate->getFormattedPriceBreakdown();

    expect($result)->toBe([]);
});

test('it returns an empty array when package rate details are not set', function () {
    $estimate = new FedexLabelEstimate([
        'raw_payload' => [
            'ratedShipmentDetails' => [
                [
                    'someOtherKey' => 'value',
                ],
            ],
        ],
    ]);

    $result = $estimate->getFormattedPriceBreakdown();

    expect($result)->toBe([]);
});

test('it returns formatted price breakdown correctly', function () {
    $estimate = new FedexLabelEstimate([
        'raw_payload' => [
            'ratedShipmentDetails' => [
                [
                    'ratedPackages' => [
                        [
                            'packageRateDetail' => [
                                'baseCharge' => 100.25,
                                'surcharges' => [
                                    ['description' => 'Fuel Surcharge', 'amount' => 15.75],
                                    ['type' => 'Residential', 'amount' => 5],
                                ],
                                'freightDiscounts' => [
                                    ['description' => 'Promo Discount', 'amount' => 10.5],
                                ],
                            ],
                        ],
                    ],
                    'totalNetCharge' => 120.50,
                ],
            ],
        ],
    ]);

    $result = $estimate->getFormattedPriceBreakdown();

    expect($result)->toBe([
        'base_rate' => '$100.25',
        'surcharges' => [
            'Fuel Surcharge' => '$15.75',
            'Residential' => '$5.00',
        ],
        'discounts' => [
            'Promo Discount discount' => '-$10.50',
        ],
        'estimated_total' => '$120.50',
    ]);
});

test('it handles missing surcharge and discount information gracefully', function () {
    $estimate = new FedexLabelEstimate([
        'raw_payload' => [
            'ratedShipmentDetails' => [
                [
                    'ratedPackages' => [
                        [
                            'packageRateDetail' => [
                                'baseCharge' => 75.00,
                            ],
                        ],
                    ],
                    'totalNetCharge' => 75,
                ],
            ],
        ],
    ]);

    $result = $estimate->getFormattedPriceBreakdown();

    expect($result)->toBe([
        'base_rate' => '$75.00',
        'surcharges' => [],
        'discounts' => [],
        'estimated_total' => '$75.00',
    ]);
});
