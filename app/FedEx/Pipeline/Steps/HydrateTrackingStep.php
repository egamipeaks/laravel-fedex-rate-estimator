<?php

namespace App\FedEx\Pipeline\Steps;

use App\FedEx\Data\Pipeline\FedexImportPipelineContext;
use App\FedEx\Services\FedExTrackingService;
use App\Models\FedexLabel;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class HydrateTrackingStep
{
    public function __construct(
        protected FedExTrackingService $trackingService,
    ) {}

    public function handle(FedexImportPipelineContext $context, Closure $next): FedexImportPipelineContext
    {
        $labels = $this->getLabelsToHydrate($context);

        if ($labels->isNotEmpty()) {
            $this->processBatches($labels);
        }

        return $next($context);
    }

    protected function getLabelsToHydrate(FedexImportPipelineContext $context): Collection
    {
        return $context->newLabels
            ->merge($context->updatedLabels)
            ->unique('id');
    }

    protected function processBatches(Collection $labels): void
    {
        $trackingNumbers = $labels->pluck('tracking_number')->unique();

        $trackingNumbers->chunk(30)->each(function (Collection $batch) use ($labels) {
            try {
                $results = data_get(
                    $this->trackingService->trackMultiple($batch->toArray()),
                    'output.completeTrackResults',
                    []
                );

                if (empty($results)) {
                    return;
                }

                $this->hydrateLabels($labels, $results);
            } catch (\Throwable $e) {
                Log::error('Error hydrating labels', [
                    'tracking_numbers' => $batch->toArray(),
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    protected function hydrateLabels(Collection $labels, array $results): void
    {
        $resultsByNumber = collect($results)->keyBy('trackingNumber');

        $labels->each(fn (FedexLabel $label) => $this->updateLabel(
            $label,
            $resultsByNumber->get($label->tracking_number)
        ));
    }

    protected function updateLabel(FedexLabel $label, ?array $result): void
    {
        if (empty($result)) {
            return;
        }

        $firstTrack = Arr::first($result['trackResults'] ?? []);

        $referenceNumber = $this->getIdentifier($firstTrack, 'CUSTOMER_REFERENCE');
        $purchaseOrder = $this->getIdentifier($firstTrack, 'PURCHASE_ORDER');

        $label->update([
            'tracking_metadata' => $result,
            'reference_number' => $referenceNumber,
            'purchase_order' => $purchaseOrder,
        ]);
    }

    protected function getIdentifier(?array $track, string $type): ?string
    {
        return collect(data_get($track, 'additionalTrackingInfo.packageIdentifiers', []))
            ->firstWhere('type', $type)['values'][0] ?? null;
    }
}
