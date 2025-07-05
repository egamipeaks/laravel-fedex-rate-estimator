<?php

namespace App\FedEx\Services;

use App\Models\FedexLabel;
use App\Models\FedexLabelEstimate;
use Illuminate\Support\Facades\Log;

class EstimateRateForLabelService
{
    public function run(FedexLabel $label): void
    {
        if ($label->isDelivered()) {
            return;
        }

        $meta = $label->raw_metadata;

        $origin = [
            'city' => $meta['Shipper city'] ?? '',
            'stateOrProvinceCode' => $meta['Shipper state'] ?? '',
            'postal_code' => $meta['Shipper postal'] ?? null,
            'residential' => false,
        ];

        $destination = [
            'city' => $meta['Recipient city'] ?? '',
            'stateOrProvinceCode' => $meta['Recipient state'] ?? '',
            'postal_code' => $meta['Recipient postal'] ?? null,
            'residential' => $label->residential ?? false,
        ];

        $package = [
            'weight_unit' => 'LB',
            'weight' => $meta['Pkg Wt (Lbs)'] ?? null,
        ];

        if ($meta['Pkg Length (in)'] ?? false) {
            $package = array_merge($package, [
                'length' => $meta['Pkg Length (in)'],
                'width' => $meta['Pkg Width (in)'],
                'height' => $meta['Pkg Height (in)'],
                'dimensions_unit' => 'IN',
            ]);
        }

        // Ensure we have the bare minimum
        if (
            empty($origin['postal_code']) ||
            empty($destination['postal_code']) ||
            empty($package['weight'])
        ) {
            return;
        }

        // Check if an identical estimate already exists
        $alreadyExists = $label->estimates()->whereJsonContains('origin', $origin)
            ->whereJsonContains('destination', $destination)
            ->whereJsonContains('package', $package)
            ->exists();

        if ($alreadyExists) {
            return;
        }

        try {
            /** @var FedExRateService $rateService */
            $rateService = app(FedExRateService::class);
            $response = $rateService->estimate($origin, $destination, $package);

            foreach ($response['output']['rateReplyDetails'] ?? [] as $rateOption) {
                $serviceType = $rateOption['serviceType'] ?? 'UNKNOWN';

                $accountDetail = collect($rateOption['ratedShipmentDetails'] ?? [])
                    ->firstWhere('rateType', 'ACCOUNT');

                $amount = $accountDetail['totalNetCharge'] ?? null;

                if ($amount !== null) {
                    FedexLabelEstimate::create([
                        'fedex_label_id' => $label->id,
                        'service_type' => $serviceType,
                        'estimate' => (int) round($amount * 100),
                        'origin' => $origin,
                        'destination' => $destination,
                        'package' => $package,
                        'raw_payload' => $rateOption,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error estimating FedEx rate', [
                'tracking_number' => $label->tracking_number,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
