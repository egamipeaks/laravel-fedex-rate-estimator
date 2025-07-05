<?php

namespace App\FedEx\Data\Pipeline;

use Illuminate\Support\Collection;

class FedexImportPipelineContext
{
    public string $csvFile;

    public array $csvRecords = [];

    public Collection $newLabels;

    public Collection $updatedLabels;

    public function __construct(string $csvFile)
    {
        $this->csvFile = $csvFile;
        $this->newLabels = collect();
        $this->updatedLabels = collect();
    }
}
