<?php

use App\Enums\FedexImportMode;
use App\FedEx\Data\Pipeline\FedexImportPipelineContext;
use App\FedEx\Services\FedexCsvImporter;
use App\FedEx\Services\FedexFileDownloader;
use App\Jobs\RefreshFedexLabelsJob;
use App\Models\FedexLabel;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;

beforeEach(function () {
    Storage::fake(); // fake the default disk
});

it('processes job successfully for manual mode', function () {

    Log::spy();

    $context = new FedexImportPipelineContext('fake-file.csv');
    $context->newLabels->push(FedexLabel::factory()->create());
    $context->updatedLabels->push(FedexLabel::factory()->create());

    $this->instance(
        FedexFileDownloader::class,
        Mockery::mock(FedexFileDownloader::class, function (MockInterface $mock) {
            $mock->shouldReceive('run')
                ->once()
                ->andReturn(true);
        })
    );

    // mock FedexCsvImporter
    $this->instance(
        FedexCsvImporter::class,
        Mockery::mock(FedexCsvImporter::class, function (MockInterface $mock) use ($context) {
            $mock->shouldReceive('run')
                ->once()
                ->with(false)
                ->andReturn($context);
        })
    );

    $job = new RefreshFedexLabelsJob(FedexImportMode::MANUAL);

    $job->handle();

    Log::shouldHaveReceived('info')
        ->with('FedEx import completed successfully. Imported 1 new records, updated 1 records.')
        ->once();
});

it('processes job successfully for scheduled mode', function () {

    Log::spy();

    $context = new FedexImportPipelineContext('fake-file.csv');
    $context->newLabels->push(FedexLabel::factory()->create());
    $context->updatedLabels->push(FedexLabel::factory()->create());

    $this->instance(
        FedexFileDownloader::class,
        Mockery::mock(FedexFileDownloader::class, function (MockInterface $mock) {
            $mock->shouldReceive('run')
                ->once()
                ->andReturn(true);
        })
    );

    // mock FedexCsvImporter
    $this->instance(
        FedexCsvImporter::class,
        Mockery::mock(FedexCsvImporter::class, function (MockInterface $mock) use ($context) {
            $mock->shouldReceive('run')
                ->once()
                ->with(true)
                ->andReturn($context);
        })
    );

    $job = new RefreshFedexLabelsJob(FedexImportMode::SCHEDULED);

    $job->handle();

    Log::shouldHaveReceived('info')
        ->with('FedEx import completed successfully. Imported 1 new records, updated 1 records.')
        ->once();
});

it('does not run if another job is running', function () {
    // Simulate that another job is already running
    $lock = Cache::lock(RefreshFedexLabelsJob::CACHE_KEY, RefreshFedexLabelsJob::TIMEOUT);
    $lock->get();

    $this->instance(
        FedexFileDownloader::class,
        Mockery::mock(FedexFileDownloader::class, function (MockInterface $mock) {
            $mock->shouldReceive('run')
                ->never()
                ->andReturn(true);
        })
    );

    $job = new RefreshFedexLabelsJob(FedexImportMode::MANUAL);

    $job->handle();

    $lock->release();
});
