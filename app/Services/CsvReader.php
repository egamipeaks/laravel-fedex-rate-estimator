<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use League\Csv\Exception;
use League\Csv\Reader;

class CsvReader
{
    /**
     * @throws Exception
     */
    public function readCsvFile(string $filePath): array
    {
        if (! Storage::exists($filePath)) {
            throw new \Exception("The file at path {$filePath} does not exist.");
        }

        $stream = Storage::readStream($filePath);
        $csv = Reader::createFromStream($stream);

        $rows = iterator_to_array($csv->getRecords(), false);

        $headers = $this->getHeaders($rows);

        return $this->processCsvRows($rows, $headers);
    }

    private function deduplicateHeaders(array $originalHeader): array
    {
        $dedupedHeader = [];
        $headerCounts = [];

        foreach ($originalHeader as $header) {
            $clean = trim($header);

            if (! isset($headerCounts[$clean])) {
                $headerCounts[$clean] = 0;
                $dedupedHeader[] = $clean;

                continue;
            }

            $headerCounts[$clean]++;
            $dedupedHeader[] = $clean.'__'.$headerCounts[$clean];
        }

        return $dedupedHeader;
    }

    private function getHeaders(array &$rows): array
    {
        $originalHeader = array_shift($rows);

        return $this->deduplicateHeaders($originalHeader);
    }

    private function processCsvRows(array $rows, array $headers): array
    {
        return collect($rows)
            ->map(fn ($row) => array_combine($headers, array_values($row)))
            ->toArray();
    }
}
