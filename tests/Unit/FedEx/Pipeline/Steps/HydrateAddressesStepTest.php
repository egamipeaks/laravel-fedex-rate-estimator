<?php

use App\FedEx\Data\Pipeline\FedexImportPipelineContext;
use App\FedEx\Pipeline\Steps\HydrateAddressesStep;
use App\FedEx\Services\FedExAddressValidationService;
use App\Models\FedexLabel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->context = new FedexImportPipelineContext('dummy-file.csv');
});

afterEach(function () {
    Mockery::close();
});

it('returns the context if no labels exist', function () {
    $addressValidationServiceMock = Mockery::mock(FedExAddressValidationService::class);
    // No labels means no call to the service.
    $step = new HydrateAddressesStep($addressValidationServiceMock);
    $result = $step->handle($this->context, fn ($ctx) => $ctx);
    expect($result)->toBe($this->context);
});

it('updates labels with correct residential flag based on validated addresses', function () {
    // Create two FedexLabel records with complete raw_metadata.
    $label1 = FedexLabel::create([
        'tracking_number' => 'TRK1',
        'raw_metadata' => [
            'Recipient address' => '123 Main St',
            'Recipient city' => 'Anytown',
            'Recipient postal' => '12345',
            'Recipient state' => 'TX',
        ],
        'residential' => null,
    ]);

    $label2 = FedexLabel::create([
        'tracking_number' => 'TRK2',
        'raw_metadata' => [
            'Recipient address' => '456 Second Ave',
            'Recipient city' => 'Othertown',
            'Recipient postal' => '67890',
            'Recipient state' => 'FL',
        ],
        'residential' => null,
    ]);

    $this->context->newLabels->push($label1, $label2);

    // Simulate a valid response from the address validation API.
    $fakeResponse = [
        'output' => [
            'resolvedAddresses' => [
                // For the first label, classification is RESIDENTIAL.
                ['classification' => 'RESIDENTIAL'],
                // For the second, classification is BUSINESS.
                ['classification' => 'BUSINESS'],
            ],
        ],
    ];

    $addressValidationServiceMock = Mockery::mock(FedExAddressValidationService::class);
    $addressValidationServiceMock->shouldReceive('validateAddresses')
        ->once()
        ->andReturn($fakeResponse);

    $step = new HydrateAddressesStep($addressValidationServiceMock);
    $step->handle($this->context, fn ($ctx) => $ctx);

    // Refresh models from database.
    $freshLabel1 = $label1->fresh();
    $freshLabel2 = $label2->fresh();

    expect($freshLabel1->residential)->toBeTrue()
        ->and($freshLabel2->residential)->toBeFalse();
});

it('logs an error and leaves labels unchanged when address validation fails', function () {
    $label = FedexLabel::create([
        'tracking_number' => 'TRK1',
        'raw_metadata' => [
            'Recipient address' => '123 Main St',
            'Recipient city' => 'Anytown',
            'Recipient postal' => '12345',
            'Recipient state' => 'TX',
        ],
        'residential' => null,
    ]);

    $this->context->newLabels->push($label);

    $addressValidationServiceMock = Mockery::mock(FedExAddressValidationService::class);
    $addressValidationServiceMock->shouldReceive('validateAddresses')
        ->once()
        ->andThrow(new Exception('Validation error'));

    Log::shouldReceive('error')
        ->once()
        ->withArgs(function ($message, $contextArray) {
            return str_contains($message, 'Error hydrating addresses')
                   && isset($contextArray['labels'])
                   && in_array('TRK1', $contextArray['labels'])
                   && isset($contextArray['error']);
        });

    $step = new HydrateAddressesStep($addressValidationServiceMock);
    $step->handle($this->context, fn ($ctx) => $ctx);

    $freshLabel = $label->fresh();
    // Since validation failed, residential remains unchanged.
    expect($freshLabel->residential)->toBeNull();
});
