<?php

namespace App\FedEx\Services;

use App\FedEx\Data\Pipeline\FedexImportPipelineContext;
use App\FedEx\Pipeline\Steps\EstimateRatesStep;
use App\FedEx\Pipeline\Steps\HydrateAddressesStep;
use App\FedEx\Pipeline\Steps\HydrateTrackingStep;
use App\FedEx\Pipeline\Steps\ImportCsvStep;
use App\FedEx\Pipeline\Steps\SendEmailStep;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FedexCsvImporter
{
    public function run(bool $sendEmail = false): ?FedexImportPipelineContext
    {
        $latestFile = $this->getLatestCsvFile();

        if (! $latestFile) {
            logger()->info('No FedEx CSV files found in storage/fedex.');

            return null;
        }

        logger()->info("Processing file: $latestFile");

        $context = new FedexImportPipelineContext($latestFile);

        $pipeline = [
            ImportCsvStep::class,
            HydrateAddressesStep::class,
            HydrateTrackingStep::class,
            EstimateRatesStep::class,
        ];

        if ($sendEmail) {
            $pipeline[] = SendEmailStep::class;
        }

        app(Pipeline::class)
            ->send($context)
            ->through($pipeline)
            ->then(function ($context) {
                return $context;
            });

        $this->archiveFile($latestFile);

        return $context;
    }

    protected function getLatestCsvFile(): ?string
    {
        $files = Storage::files('fedex');

        return collect($files)
            ->filter(fn ($f) => Str::endsWith($f, '.csv'))
            ->sortByDesc(fn ($f) => Storage::lastModified($f))
            ->first();
    }

    protected function archiveFile(string $filePath): void
    {
        $timestamp = now()->format('Ymd_His');
        $filename = basename($filePath);
        $archivedPath = "private/fedex/archive/{$timestamp}_{$filename}";

        Storage::move($filePath, $archivedPath);

        logger()->info("Archived file to: $archivedPath");
    }
}
