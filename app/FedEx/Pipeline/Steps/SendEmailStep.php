<?php

namespace App\FedEx\Pipeline\Steps;

use App\FedEx\Data\Pipeline\FedexImportPipelineContext;
use App\Mail\FedexImportSummaryTableMail;
use Illuminate\Support\Facades\Mail;

class SendEmailStep
{
    public function handle(FedexImportPipelineContext $context, \Closure $next)
    {
        if ($context->newLabels->isNotEmpty() || $context->updatedLabels->isNotEmpty()) {
            Mail::to(config('services.fedex.reports.email', 'your-client@example.com'))
                ->send(new FedexImportSummaryTableMail($context->newLabels, $context->updatedLabels));
        }

        return $next($context);
    }
}
