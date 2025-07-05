<?php

namespace App\FedEx\Pipeline\Steps;

use App\FedEx\Data\Pipeline\FedexImportPipelineContext;
use App\FedEx\Services\EstimateRateForLabelService;
use App\FedEx\Services\FedExRateService;

class EstimateRatesStep
{
    protected FedExRateService $rateService;

    public function __construct(FedExRateService $rateService)
    {
        $this->rateService = $rateService;
    }

    public function handle(FedexImportPipelineContext $context, \Closure $next)
    {
        $labelsToRate = $context->newLabels->merge($context->updatedLabels);

        foreach ($labelsToRate as $label) {
            /** @var \App\Models\FedexLabel $label */
            $label->refresh();

            app(EstimateRateForLabelService::class)->run($label);
        }

        return $next($context);
    }
}
