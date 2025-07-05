<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FedexLabelEstimate extends Model
{
    use HasFactory;

    protected $fillable = [
        'fedex_label_id',
        'service_type',
        'estimate',
        'origin',
        'destination',
        'package',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'origin' => 'array',
            'destination' => 'array',
            'package' => 'array',
            'raw_payload' => 'array',
        ];
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(FedexLabel::class, 'fedex_label_id');
    }

    public function getFormattedPriceBreakdown(): array
    {
        if (! isset($this->raw_payload['ratedShipmentDetails'][0])) {
            return [];
        }

        $shipment = $this->raw_payload['ratedShipmentDetails'][0];

        if (! isset($shipment['ratedPackages'][0]['packageRateDetail'])) {
            return [];
        }

        $detail = $shipment['ratedPackages'][0]['packageRateDetail'];

        $baseRate = $detail['baseCharge'] ?? 0;
        $surcharges = [];

        if (isset($detail['surcharges']) && is_array($detail['surcharges'])) {
            foreach ($detail['surcharges'] as $surcharge) {
                $desc = $surcharge['description'] ?? $surcharge['type'] ?? 'Surcharge';
                $amount = $surcharge['amount'] ?? 0;
                $surcharges[$desc] = '$'.number_format($amount, 2);
            }
        }

        $discounts = [];

        if (isset($detail['freightDiscounts']) && is_array($detail['freightDiscounts'])) {
            foreach ($detail['freightDiscounts'] as $discount) {
                $desc = $discount['description'] ?? $discount['type'] ?? 'Discount';
                $amount = $discount['amount'] ?? 0;
                $discounts[$desc.' discount'] = '-$'.number_format($amount, 2);
            }
        }

        $estimatedTotal = $shipment['totalNetCharge'] ?? 0;

        return [
            'base_rate' => '$'.number_format($baseRate, 2),
            'surcharges' => $surcharges,
            'discounts' => $discounts,
            'estimated_total' => '$'.number_format($estimatedTotal, 2),
        ];
    }
}
