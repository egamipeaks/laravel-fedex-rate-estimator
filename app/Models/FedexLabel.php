<?php

namespace App\Models;

namespace App\Models;

use App\Enums\FedexLabelStatus;
use App\Models\Traits\HasFedexMetaAttributes;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FedexLabel extends Model
{
    use HasFactory;
    use HasFedexMetaAttributes;

    protected $fillable = [
        'tracking_number',
        'status',
        'raw_metadata',
        'residential',
        'tracking_metadata',
        'purchase_order',
        'reference_number',
    ];

    protected function casts(): array
    {
        return [
            'raw_metadata' => 'array',
            'tracking_metadata' => 'array',
            'status' => FedexLabelStatus::class,
            'residential' => 'boolean',
        ];
    }

    #[Scope]
    protected function delivered($query)
    {
        return $query->where('status', FedexLabelStatus::Delivered);
    }

    #[Scope]
    protected function notDelivered($query)
    {
        return $query
            ->where('status', '!=', FedexLabelStatus::Delivered)
            ->where('status', '!=', FedexLabelStatus::Cancelled);
    }

    #[Scope]
    protected function cancelled($query)
    {
        return $query->where('status', FedexLabelStatus::Cancelled);
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(FedexLabelEstimate::class);
    }

    public function latestEstimate(): HasOne
    {
        return $this->hasOne(FedexLabelEstimate::class)->latestOfMany();
    }

    public function getEstimateDataAttribute()
    {
        $priceBreakdown = $this->latestEstimate?->getFormattedPriceBreakdown() ?? [];

        $data = [];

        $data['Base Rate'] = $priceBreakdown['base_rate'] ?? 0;

        $surcharges = $priceBreakdown['surcharges'] ?? [];

        if ($surcharges) {
            foreach ($surcharges as $label => $surcharge) {
                $data[$label] = $surcharge;
            }
        }

        $discounts = $priceBreakdown['discounts'] ?? [];

        if ($discounts) {
            foreach ($discounts as $label => $discount) {
                $data[$label] = $discount;
            }
        }

        $data['Estimated Total'] = $priceBreakdown['estimated_total'] ?? 0;

        return $data;
    }

    public function isDelivered(): bool
    {
        return $this->status === FedexLabelStatus::Delivered;
    }

    public function getStatusIconAttribute(): string
    {
        return $this->status?->icon() ?? '';
    }

    public function getTrackingLinkAttribute(): string
    {
        return "https://www.fedex.com/apps/fedextrack/?action=track&tracknumbers={$this->tracking_number}&clienttype=ivother";
    }
}
