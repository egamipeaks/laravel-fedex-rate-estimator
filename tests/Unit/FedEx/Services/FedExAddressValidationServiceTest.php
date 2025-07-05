<?php

use App\FedEx\Services\FedExAddressValidationService;
use App\FedEx\Services\FedExRatesConnector;
use Saloon\Http\Response;
use ShipStream\FedEx\Api\AddressValidationV1\Api;

afterEach(function () {
    Mockery::close();
});

it('validates addresses and returns expected JSON output', function () {
    $mockResponse = Mockery::mock(Response::class);
    $mockResponse->shouldReceive('json')
        ->once()
        ->andReturn(['output' => 'validated addresses']);

    $mockApi = Mockery::mock(Api::class);
    $mockApi->shouldReceive('validateAddress')
        ->once()
        ->andReturn($mockResponse);

    $mockFedEx = Mockery::mock(FedExRatesConnector::class);
    $mockFedEx->shouldReceive('addressValidationV1')
        ->once()
        ->andReturn($mockApi);

    app()->instance(FedExRatesConnector::class, $mockFedEx);

    $service = app(FedExAddressValidationService::class);

    $addresses = [
        [
            'streetLines' => '9300 BAMBOO RD',
            'city' => 'HOUSTON',
            'postalCode' => '77041',
            'stateOrProvinceCode' => 'TX',
            'countryCode' => 'US',
        ],
        [
            'streetLines' => ['5405 CYPRESS CENTER DR STE 200'],
            'city' => 'TAMPA',
            'postalCode' => '33609',
            'stateOrProvinceCode' => 'FL',
            'countryCode' => 'US',
        ],
    ];

    $result = $service->validateAddresses($addresses);

    expect($result)->toBe(['output' => 'validated addresses']);
});

it('throws InvalidArgumentException when missing required address data', function () {
    $mockResponse = Mockery::mock(Response::class);
    $mockResponse->shouldReceive('json')
        ->andReturn(['output' => 'validated addresses']);

    $mockApi = Mockery::mock(Api::class);
    $mockApi->shouldReceive('validateAddress')
        ->andReturn($mockResponse);

    $mockFedEx = Mockery::mock(FedExRatesConnector::class);
    $mockFedEx->shouldReceive('addressValidationV1')
        ->andReturn($mockApi);

    app()->instance(FedExRatesConnector::class, $mockFedEx);

    $service = app(FedExAddressValidationService::class);

    $addresses = [
        [
            'streetLines' => '9300 BAMBOO RD',
            // Missing city field.
            'postalCode' => '77041',
            'stateOrProvinceCode' => 'TX',
            'countryCode' => 'US',
        ],
    ];

    expect(fn () => $service->validateAddresses($addresses))
        ->toThrow(InvalidArgumentException::class, 'Invalid address data provided.');
});
