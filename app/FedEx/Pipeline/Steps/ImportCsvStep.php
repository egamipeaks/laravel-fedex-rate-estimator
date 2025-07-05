<?php

namespace App\FedEx\Pipeline\Steps;

use App\FedEx\Data\Pipeline\FedexImportPipelineContext;
use App\Models\FedexLabel;
use App\Services\CsvReader;
use Illuminate\Support\Facades\Log;
use League\Csv\Exception;

class ImportCsvStep
{
    protected FedexImportPipelineContext $context;

    public function __construct(protected CsvReader $csvReader)
    {
        //
    }

    /**
     * @throws Exception
     */
    public function handle(FedexImportPipelineContext $context, \Closure $next)
    {
        $this->context = $context;
        $this->context->csvRecords = $this->csvReader->readCsvFile($this->context->csvFile) ?? [];

        if (empty($this->context->csvRecords)) {
            Log::warning('CSV is empty.');

            return $next($context);
        }

        $this->processLabels();

        return $next($this->context);
    }

    protected function processLabels(): void
    {
        foreach ($this->context->csvRecords as $record) {
            $trackingNumber = trim($record['Tracking Number'] ?? '');

            if (! $trackingNumber) {
                continue;
            }

            $this->processLabel($trackingNumber, $record);
        }
    }

    protected function processLabel(string $trackingNumber, array $record): void
    {
        $status = trim($record['Status'] ?? $record['Status '] ?? '');

        try {
            $existing = FedexLabel::where('tracking_number', $trackingNumber)->first();

            if (! $existing) {
                $this->createNewLabel($trackingNumber, $status, $record);

                return;
            }

            $this->updateExistingLabelIfChanged($existing, $status, $record);

        } catch (\Throwable $e) {
            Log::error('Error importing FedEx label', [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function createNewLabel(string $trackingNumber, string $status, array $record): void
    {
        $label = FedexLabel::create([
            'tracking_number' => $trackingNumber,
            'status' => $status,
            'raw_metadata' => $record,
        ]);

        $this->context->newLabels->push($label);
    }

    protected function updateExistingLabelIfChanged(FedexLabel $existing, string $status, array $record): void
    {
        $existingData = $this->ksortRecursive($existing->raw_metadata);
        $incomingData = $this->ksortRecursive($record);

        $hasChanged = $existing->status->value !== $status || $existingData !== $incomingData;

        if (! $hasChanged) {
            return;
        }

        $existing->update([
            'status' => $status,
            'raw_metadata' => $record,
        ]);

        $this->context->updatedLabels->push($existing);
    }

    protected function ksortRecursive(array $array): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = $this->ksortRecursive($value);
            }
        }

        ksort($array);

        return $array;
    }
}
