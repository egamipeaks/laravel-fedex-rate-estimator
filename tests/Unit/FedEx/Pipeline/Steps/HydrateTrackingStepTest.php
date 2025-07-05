<?php

use App\Enums\FedexLabelStatus;
use App\FedEx\Data\Pipeline\FedexImportPipelineContext;
use App\FedEx\Pipeline\Steps\HydrateTrackingStep;
use App\FedEx\Services\FedExTrackingService;
use App\Models\FedexLabel;
use App\Models\FedexLabelEstimate;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->trackingService = Mockery::mock(FedExTrackingService::class);
    $this->step = new HydrateTrackingStep($this->trackingService);
    $this->context = new FedexImportPipelineContext('test.csv');
    $this->next = fn ($context) => $context;

    FedexLabel::query()->delete();
    FedexLabelEstimate::query()->delete();
});

afterEach(function () {
    Mockery::close();
});

it('sets reference number and purchase number', function () {
    // Arrange
    $label = FedexLabel::create([
        'tracking_number' => '234523452345',
        'status' => FedexLabelStatus::OnTheWay,
        'reference_number' => null,
    ]);

    $this->context->newLabels = collect([$label]);

    $mockResponse = json_decode(file_get_contents(base_path('tests/MockData/tracking-data.json')), true);

    $this->trackingService
        ->shouldReceive('trackMultiple')
        ->once()
        ->with(['234523452345'])
        ->andReturn($mockResponse);

    // Act
    $this->step->handle($this->context, $this->next);

    // Assert
    expect($label->fresh()->reference_number)->toBe('COMP11233')
        ->and($label->fresh()->purchase_order)->toBe('COMP11233');
});

it('handles multiple labels in batches', function () {
    // Arrange
    $labels = [];

    for ($i = 1; $i <= 35; $i++) {
        $labels[] = FedexLabel::create([
            'tracking_number' => '1234567890'.$i,
            'status' => FedexLabelStatus::OnTheWay,
        ]);
    }

    $this->context->newLabels = collect($labels);

    $mockResponse = [
        'output' => [
            'completeTrackResults' => collect($labels)->map(fn ($label) => [
                'trackingNumber' => $label->tracking_number,
                'trackResults' => [
                    [
                        'recipientInformation' => [
                            'address' => [
                                'residential' => true,
                            ],
                        ],
                    ],
                ],
            ])->toArray(),
        ],
    ];

    $this->trackingService
        ->shouldReceive('trackMultiple')
        ->twice()
        ->andReturn($mockResponse);

    $this->step->handle($this->context, $this->next);

    foreach ($labels as $label) {
        $trackingMetadata = collect($mockResponse['output']['completeTrackResults'])
            ->firstWhere('trackingNumber', $label->tracking_number);

        $labelData = $label->fresh()->tracking_metadata;

        expect($labelData['trackingNumber'])->toBe($trackingMetadata['trackingNumber'])
            ->and($labelData['trackResults'][0]['recipientInformation']['address']['residential'])->toBe(true);
    }
});

it('logs error and continues on tracking service failure', function () {
    // Arrange
    $label = FedexLabel::create([
        'tracking_number' => '123456789012',
        'status' => FedexLabelStatus::OnTheWay,
    ]);

    $this->context->newLabels = collect([$label]);

    $this->trackingService
        ->shouldReceive('trackMultiple')
        ->once()
        ->with(['123456789012'])
        ->andThrow(new \Exception('API Error'));

    Log::spy();

    // Act
    $result = $this->step->handle($this->context, $this->next);

    // Assert
    expect($result)->toBe($this->context)
        ->and($label->fresh()->residential)->toBeNull();
});

it('skips labels with missing tracking number in response', function () {
    // Arrange
    $label = FedexLabel::create([
        'tracking_number' => '123456789012',
        'status' => \App\Enums\FedexLabelStatus::OnTheWay,
        'residential' => null,
    ]);

    $this->context->newLabels = collect([$label]);

    $mockResponse = [
        'output' => [
            'completeTrackResults' => [
                [
                    'trackResults' => [
                        [
                            'recipientInformation' => [
                                'address' => [
                                    'residential' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $this->trackingService
        ->shouldReceive('trackMultiple')
        ->once()
        ->with(['123456789012'])
        ->andReturn($mockResponse);

    // Act
    $this->step->handle($this->context, $this->next);

    // Assert
    expect($label->fresh()->residential)->toBeNull();
});

it('handles empty tracking numbers gracefully', function () {
    // Arrange
    $this->context->newLabels = collect([]);
    $this->context->updatedLabels = collect([]);

    $this->trackingService
        ->shouldNotReceive('trackMultiple');

    // Act
    $result = $this->step->handle($this->context, $this->next);

    // Assert
    expect($result)->toBe($this->context);
});
