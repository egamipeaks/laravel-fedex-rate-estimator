<?php

use App\FedEx\Data\Pipeline\FedexImportPipelineContext;
use App\FedEx\Pipeline\Steps\EstimateRatesStep;
use App\FedEx\Pipeline\Steps\HydrateAddressesStep;
use App\FedEx\Pipeline\Steps\HydrateTrackingStep;
use App\FedEx\Pipeline\Steps\ImportCsvStep;
use App\FedEx\Pipeline\Steps\SendEmailStep;
use App\FedEx\Services\FedexCsvImporter;
use Carbon\Carbon;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake(); // fake the default disk

    // Freeze time for predictable archive timestamps
    Carbon::setTestNow(Carbon::parse('2025-05-21 12:00:00'));
});

afterEach(function () {
    Mockery::close();
});

it('returns null when no CSV files are found', function () {
    Storage::shouldReceive('files')
        ->with('fedex')
        ->once()
        ->andReturn([]);

    $importer = new FedexCsvImporter;
    expect($importer->run())->toBeNull();
});

it('runs pipeline and archives the latest CSV file without sending email', function () {
    // Prepare two files, ensuring new.csv is the most recent
    Storage::shouldReceive('files')
        ->with('fedex')
        ->once()
        ->andReturn(['fedex/old.csv', 'fedex/new.csv']);

    Storage::shouldReceive('lastModified')
        ->with('fedex/old.csv')
        ->andReturn(100);

    Storage::shouldReceive('lastModified')
        ->with('fedex/new.csv')
        ->andReturn(200);

    // Expect archive move with timestamped filename
    $timestamp = Carbon::now()->format('Ymd_His');
    $expected = "private/fedex/archive/{$timestamp}_new.csv";
    Storage::shouldReceive('move')
        ->once()
        ->with('fedex/new.csv', $expected);

    // Mock the pipeline to capture pipeline steps
    $pipeline = Mockery::mock(Pipeline::class);
    app()->instance(Pipeline::class, $pipeline);

    $pipeline->shouldReceive('send')
        ->once()
        ->with(Mockery::on(fn ($context) => $context instanceof FedexImportPipelineContext
            && $context->csvFile === 'fedex/new.csv'
        ))
        ->andReturnSelf();

    $pipeline->shouldReceive('through')
        ->once()
        ->with([
            ImportCsvStep::class,
            HydrateAddressesStep::class,
            HydrateTrackingStep::class,
            EstimateRatesStep::class,
        ])
        ->andReturnSelf();

    $pipeline->shouldReceive('then')
        ->once()
        ->andReturnUsing(fn ($callback) => $callback(new FedexImportPipelineContext('fedex/new.csv')));

    $importer = new FedexCsvImporter;
    $context = $importer->run(false);

    expect($context)->toBeInstanceOf(FedexImportPipelineContext::class)
        ->and($context->csvFile)->toBe('fedex/new.csv');
});

it('includes SendEmailStep when sendEmail is true', function () {
    // Similar setup as previous test
    Storage::shouldReceive('files')->andReturn(['fedex/data.csv']);
    Storage::shouldReceive('lastModified')->andReturn(123);
    Storage::shouldReceive('move')->andReturnTrue();

    $pipeline = Mockery::mock(Pipeline::class);
    app()->instance(Pipeline::class, $pipeline);

    $pipeline->shouldReceive('send')->andReturnSelf();
    $pipeline->shouldReceive('through')
        ->with([
            ImportCsvStep::class,
            HydrateAddressesStep::class,
            HydrateTrackingStep::class,
            EstimateRatesStep::class,
            SendEmailStep::class,
        ])
        ->once()
        ->andReturnSelf();

    $pipeline->shouldReceive('then')
        ->andReturnUsing(fn ($cb) => $cb(new FedexImportPipelineContext('fedex/data.csv')));

    $importer = new FedexCsvImporter;
    $context = $importer->run(true);

    expect($context)->toBeInstanceOf(FedexImportPipelineContext::class);
});
