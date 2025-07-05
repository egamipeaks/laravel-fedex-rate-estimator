<?php

use App\FedEx\Services\EstimateRateForLabelService;
use App\FedEx\Services\FedExRateService;
use App\Models\FedexLabel;
use App\Models\FedexLabelEstimate;
use Illuminate\Support\Facades\Log;

afterEach(function () {
    Mockery::close();
});

it('creates a rate estimate when label has all required data and no existing match', function () {
    $label = FedexLabel::factory()->create([
        'status' => 'Label created',
        'raw_metadata' => [
            'Shipper city' => 'Dallas',
            'Shipper state' => 'TX',
            'Shipper postal' => '75201',
            'Recipient city' => 'Houston',
            'Recipient state' => 'TX',
            'Recipient postal' => '77001',
            'Pkg Wt (Lbs)' => '5.0',
            'Pkg Length (in)' => '10',
            'Pkg Width (in)' => '5',
            'Pkg Height (in)' => '6',
        ],
    ]);

    $mock = Mockery::mock(FedExRateService::class);
    $mock->shouldReceive('estimate')->once()->andReturn([
        'output' => [
            'rateReplyDetails' => [
                [
                    'serviceType' => 'FEDEX_GROUND',
                    'ratedShipmentDetails' => [
                        [
                            'rateType' => 'ACCOUNT',
                            'totalNetCharge' => 12.34,
                        ],
                    ],
                ],
            ],
        ],
    ]);
    app()->instance(FedExRateService::class, $mock);

    app(EstimateRateForLabelService::class)->run($label);

    expect(FedexLabelEstimate::count())->toBe(1)
        ->and(FedexLabelEstimate::first()->estimate)->toBe(1234)
        ->and(FedexLabelEstimate::first()->service_type)->toBe('FEDEX_GROUND');
});

it('skips estimate if already delivered', function () {
    $label = FedexLabel::factory()->create([
        'status' => 'Delivered',
        'raw_metadata' => [
            'Shipper postal' => '75201',
            'Recipient postal' => '77001',
            'Pkg Wt (Lbs)' => '5.0',
        ],
    ]);

    $mock = Mockery::mock(FedExRateService::class);
    $mock->shouldNotReceive('estimate');
    app()->instance(FedExRateService::class, $mock);

    app(EstimateRateForLabelService::class)->run($label);

    expect(FedexLabelEstimate::count())->toBe(0);
});

it('skips estimate if data is missing', function () {
    $label = FedexLabel::factory()->create([
        'status' => 'On the way',
        'raw_metadata' => [
            'Pkg Wt (Lbs)' => '5.0',
            'Recipient postal' => null,
        ],
    ]);

    $mock = Mockery::mock(FedExRateService::class);
    $mock->shouldNotReceive('estimate');
    app()->instance(FedExRateService::class, $mock);

    app(EstimateRateForLabelService::class)->run($label);

    expect(FedexLabelEstimate::count())->toBe(0);
});

it('does not re-run identical estimate', function () {
    $label = FedexLabel::factory()->create([
        'status' => 'Label created',
        'raw_metadata' => [
            'Shipper city' => 'Dallas',
            'Shipper state' => 'TX',
            'Shipper postal' => '75201',
            'Recipient city' => 'Houston',
            'Recipient state' => 'TX',
            'Recipient postal' => '77001',
            'Pkg Wt (Lbs)' => '5.0',
        ],
        'residential' => false,
    ]);

    $label->estimates()->create([
        'service_type' => 'FEDEX_GROUND',
        'estimate' => 1000,
        'origin' => [
            'city' => 'Dallas',
            'stateOrProvinceCode' => 'TX',
            'postal_code' => '75201',
            'residential' => false,
        ],
        'destination' => [
            'city' => 'Houston',
            'stateOrProvinceCode' => 'TX',
            'postal_code' => '77001',
            'residential' => false,
        ],
        'package' => [
            'weight_unit' => 'LB',
            'weight' => '5.0',
        ],
        'raw_payload' => [],
    ]);

    $mock = Mockery::mock(FedExRateService::class);
    $mock->shouldNotReceive('estimate');
    app()->instance(FedExRateService::class, $mock);

    app(EstimateRateForLabelService::class)->run($label);

    expect(FedexLabelEstimate::count())->toBe(1);
});

it('logs errors but does not crash', function () {
    Log::spy();

    $label = FedexLabel::factory()->create([
        'status' => 'Label created',
        'raw_metadata' => [
            'Shipper city' => 'Dallas',
            'Shipper state' => 'TX',
            'Shipper postal' => '75201',
            'Recipient city' => 'Houston',
            'Recipient state' => 'TX',
            'Recipient postal' => '77001',
            'Pkg Wt (Lbs)' => '5.0',
        ],
    ]);

    $mock = Mockery::mock(FedExRateService::class);
    $mock->shouldReceive('estimate')->andThrow(new Exception('API Error'));
    app()->instance(FedExRateService::class, $mock);

    app(EstimateRateForLabelService::class)->run($label);

    Log::shouldHaveReceived('error')->once();
});
