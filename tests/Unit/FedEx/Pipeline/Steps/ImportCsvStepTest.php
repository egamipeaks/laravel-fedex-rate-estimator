<?php

use App\Enums\FedexLabelStatus;
use App\FedEx\Data\Pipeline\FedexImportPipelineContext;
use App\FedEx\Pipeline\Steps\ImportCsvStep;
use App\Models\FedexLabel;
use App\Services\CsvReader;

beforeEach(function () {
    $this->context = new FedexImportPipelineContext('dummy.csv');
});

afterEach(function () {
    Mockery::close();
});

it('skips processing when CSV is empty', function () {
    // Arrange: Create a CsvReader stub that returns an empty array.
    $csvReaderMock = Mockery::mock(CsvReader::class);
    $csvReaderMock->shouldReceive('readCsvFile')
        ->once()
        ->with('dummy.csv')
        ->andReturn([]);

    $importStep = new ImportCsvStep($csvReaderMock);

    // Act: Run the pipeline step.
    $result = $importStep->handle($this->context, function ($ctx) {
        return $ctx;
    });

    // Assert: CSV records are empty, and no new or updated labels were created.
    expect($result->csvRecords)->toEqual([])
        ->and($result->newLabels)->toEqual(collect())
        ->and($result->updatedLabels)->toEqual(collect());
});

it('creates a new label when it does not exist', function () {
    $record = [
        'Tracking Number' => '1234567890',
        'Status' => FedexLabelStatus::OnTheWay->value,
        'Other Field' => 'Value',
    ];

    $csvRecords = [$record];

    $csvReaderMock = Mockery::mock(CsvReader::class);
    $csvReaderMock->shouldReceive('readCsvFile')
        ->once()
        ->with('dummy.csv')
        ->andReturn($csvRecords);

    $importStep = new ImportCsvStep($csvReaderMock);

    $result = $importStep->handle($this->context, fn ($ctx) => $ctx);

    // Assert: A new FedexLabel should be created in the database.
    expect(FedexLabel::where('tracking_number', '1234567890')->exists())->toBeTrue();

    // And the pipeline context should contain that label in the newLabels collection.
    $newLabel = $result->newLabels->first();

    expect($newLabel->tracking_number)->toEqual('1234567890')
        ->and($newLabel->status)->toEqual(FedexLabelStatus::OnTheWay)
        ->and($newLabel->raw_metadata)->toEqual([
            'Tracking Number' => '1234567890',
            'Status' => FedexLabelStatus::OnTheWay->value,
            'Other Field' => 'Value',
        ]);
});

it('updates an existing label if changed', function () {
    $existingLabel = FedexLabel::create([
        'tracking_number' => '1234567890',
        'status' => FedexLabelStatus::OnTheWay->value,
        'raw_metadata' => [
            'Tracking Number' => '1234567890',
            'Status' => FedexLabelStatus::OnTheWay->value,
            'Other Field' => 'OldValue',
        ],
    ]);

    $newRecord = [
        'Tracking Number' => '1234567890',
        'Status' => FedexLabelStatus::RunningLate->value,
        'Other Field' => 'NewValue',
    ];

    $csvRecords = [$newRecord];

    $csvReaderMock = Mockery::mock(CsvReader::class);
    $csvReaderMock->shouldReceive('readCsvFile')
        ->once()
        ->with('dummy.csv')
        ->andReturn($csvRecords);

    $importStep = new ImportCsvStep($csvReaderMock);

    $result = $importStep->handle($this->context, fn ($ctx) => $ctx);

    $updatedLabel = FedexLabel::where('tracking_number', '1234567890')->first();

    expect($updatedLabel)->not->toBeNull()
        ->and($updatedLabel->status)->toEqual(FedexLabelStatus::RunningLate)
        ->and($updatedLabel->raw_metadata['Tracking Number'])->toEqual('1234567890')
        ->and($updatedLabel->raw_metadata['Status'])->toEqual(FedexLabelStatus::RunningLate->value)
        ->and($updatedLabel->raw_metadata['Other Field'])->toEqual('NewValue')
        ->and($result->updatedLabels->first()->tracking_number)->toEqual('1234567890');

});

it('does not update an existing label if there are no changes', function () {
    // Arrange: Create an existing label.
    $existingLabel = FedexLabel::create([
        'tracking_number' => '1234567890',
        'status' => FedexLabelStatus::OnTheWay->value,
        'raw_metadata' => [
            'Tracking Number' => '1234567890',
            'Status' => FedexLabelStatus::OnTheWay->value,
            'Other Field' => 'SameValue',
        ],
    ]);

    // Prepare a CSV record identical to the existing label.
    $record = [
        'Tracking Number' => '1234567890',
        'Status' => FedexLabelStatus::OnTheWay->value,
        'Other Field' => 'SameValue',
    ];

    $csvRecords = [$record];

    $csvReaderMock = Mockery::mock(CsvReader::class);
    $csvReaderMock->shouldReceive('readCsvFile')
        ->once()
        ->with('dummy.csv')
        ->andReturn($csvRecords);

    $importStep = new ImportCsvStep($csvReaderMock);

    $result = $importStep->handle($this->context, fn ($ctx) => $ctx);

    expect($result->updatedLabels->isEmpty())->toBeTrue()
        ->and($result->newLabels->isEmpty())->toBeTrue();
});

it('skips processing for records with no tracking number', function () {
    $record = [
        'Tracking Number' => '   ', // blank after trim
        'Status' => FedexLabelStatus::OnTheWay->value,
    ];

    $csvRecords = [$record];

    $csvReaderMock = Mockery::mock(CsvReader::class);
    $csvReaderMock->shouldReceive('readCsvFile')
        ->once()
        ->with('dummy.csv')
        ->andReturn($csvRecords);

    $importStep = new ImportCsvStep($csvReaderMock);

    $result = $importStep->handle($this->context, fn ($ctx) => $ctx);

    expect(FedexLabel::count())->toEqual(0)
        ->and($result->newLabels->isEmpty())->toBeTrue()
        ->and($result->updatedLabels->isEmpty())->toBeTrue();
});
