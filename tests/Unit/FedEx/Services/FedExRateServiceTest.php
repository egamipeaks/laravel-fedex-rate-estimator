<?php

use App\FedEx\Services\FedExRatesConnector;
use App\FedEx\Services\FedExRateService;
use ShipStream\FedEx\Api\RatesAndTransitTimesV1\Api;

afterEach(function () {
    Mockery::close();
});

it('estimates a rate using provided origin, destination, and package', function () {
    $mockResponse = Mockery::mock(\Saloon\Http\Response::class);
    $mockResponse->shouldReceive('json')
        ->once()
        ->andReturn(['output' => 'estimate result']);

    $mockApi = Mockery::mock(Api::class);
    $mockApi->shouldReceive('rateAndTransitTimes')
        ->once()
        ->andReturn($mockResponse);

    $mockFedEx = Mockery::mock(FedExRatesConnector::class);
    $mockFedEx->shouldReceive('ratesTransitTimesV1')
        ->andReturn($mockApi);

    app()->instance(FedExRatesConnector::class, $mockFedEx);

    $service = app(FedExRateService::class);

    $result = $service->estimate([
        'city' => 'Dallas',
        'stateOrProvinceCode' => 'TX',
        'postal_code' => '75201',
    ], [
        'city' => 'Houston',
        'stateOrProvinceCode' => 'TX',
        'postal_code' => '77001',
    ], [
        'weight_unit' => 'LB',
        'weight' => '5.0',
        'length' => '10',
        'width' => '5',
        'height' => '6',
        'dimensions_unit' => 'IN',
    ]);

    expect($result)->toBe(['output' => 'estimate result']);
});
