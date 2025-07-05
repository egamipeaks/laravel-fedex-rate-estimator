<?php

namespace App\FedEx\Pipeline\Steps;

use App\FedEx\Data\Pipeline\FedexImportPipelineContext;
use App\FedEx\Services\FedExAddressValidationService;
use App\Models\FedexLabel;
use Illuminate\Support\Facades\Log;

class HydrateAddressesStep
{
    protected FedExAddressValidationService $addressValidationService;

    public function __construct(FedExAddressValidationService $addressValidationService)
    {
        $this->addressValidationService = $addressValidationService;
    }

    public function handle(FedexImportPipelineContext $context, \Closure $next)
    {
        $labels = $context->newLabels->merge($context->updatedLabels);

        if ($labels->isEmpty()) {
            return $next($context);
        }

        $batches = array_chunk($labels->all(), 100);

        foreach ($batches as $batch) {
            try {
                $addresses = [];
                $mapping = [];

                foreach ($batch as $label) {
                    $meta = $label->raw_metadata;
                    $street = $meta['Recipient address'] ?? '';
                    $city = $meta['Recipient city'] ?? '';
                    $postal = $meta['Recipient postal'] ?? '';
                    $state = $meta['Recipient state'] ?? '';

                    if (! trim($street) || ! $city || ! $postal || ! $state) {
                        continue;
                    }

                    $address = [
                        'streetLines' => trim($street),
                        'city' => $city,
                        'postalCode' => $postal,
                        'stateOrProvinceCode' => $state,
                    ];

                    $mapping[] = [
                        'label' => $label,
                        'address' => $address,
                    ];

                    $addresses[] = $address;
                }

                if (empty($addresses)) {
                    continue;
                }

                $response = $this->addressValidationService->validateAddresses($addresses);
                $resolved = $response['output']['resolvedAddresses'] ?? [];

                foreach ($resolved as $index => $resolvedAddress) {
                    /** @var FedexLabel $label */
                    $label = $mapping[$index]['label'] ?? null;
                    $address = $mapping[$index]['address'] ?? null;

                    $flag = $this->addressIsResidential($resolvedAddress);

                    if ($address && $label) {
                        $label->update(['residential' => $flag]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Error hydrating addresses', [
                    'labels' => collect($batch)->pluck('tracking_number')->toArray(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $next($context);
    }

    private function addressIsResidential(mixed $resolvedAddress): bool
    {
        $addressClassification = $resolvedAddress['classification'] ?? null;

        return $addressClassification === 'RESIDENTIAL';
    }
}
