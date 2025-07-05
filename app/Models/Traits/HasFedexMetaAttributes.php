<?php

namespace App\Models\Traits;

trait HasFedexMetaAttributes
{
    public function getShipperNameAttribute(): ?string
    {
        return $this->raw_metadata['Shipper company'] ?? null;
    }

    public function getRecipientNameAttribute(): ?string
    {
        return $this->raw_metadata['Recipient company'] ?? null;
    }

    public function getRecipientCityStateAttribute(): ?string
    {
        $city = $this->raw_metadata['Recipient city'] ?? null;
        $state = $this->raw_metadata['Recipient state'] ?? null;

        return $city && $state ? "$city, $state" : null;
    }

    public function getWeightAttribute(): ?string
    {
        $weight = $this->raw_metadata['Pkg Wt (Lbs)'] ?? null;

        return $weight ? "$weight lbs" : null;
    }

    public function getServiceTypeAttribute(): ?string
    {
        return $this->raw_metadata['Service type'] ?? null;
    }

    public function getShipperCityAttribute(): ?string
    {
        return $this->raw_metadata['Shipper city'] ?? null;
    }

    public function getShipperStateAttribute(): ?string
    {
        return $this->raw_metadata['Shipper state'] ?? null;
    }

    public function getShipperPostalAttribute(): ?string
    {
        return $this->raw_metadata['Shipper postal'] ?? null;
    }

    public function getRecipientCityAttribute(): ?string
    {
        return $this->raw_metadata['Recipient city'] ?? null;
    }

    public function getRecipientStateAttribute(): ?string
    {
        return $this->raw_metadata['Recipient state'] ?? null;
    }

    public function getRecipientPostalAttribute(): ?string
    {
        return $this->raw_metadata['Recipient postal'] ?? null;
    }

    public function getWeightLbsAttribute(): ?float
    {
        return isset($this->raw_metadata['Pkg Wt (Lbs)'])
            ? (float) $this->raw_metadata['Pkg Wt (Lbs)']
            : null;
    }

    public function getPackageLengthAttribute(): ?float
    {
        return isset($this->raw_metadata['Pkg Length (in)'])
            ? (float) $this->raw_metadata['Pkg Length (in)']
            : null;
    }

    public function getPackageWidthAttribute(): ?float
    {
        return isset($this->raw_metadata['Pkg Width (in)'])
            ? (float) $this->raw_metadata['Pkg Width (in)']
            : null;
    }

    public function getPackageHeightAttribute(): ?float
    {
        return isset($this->raw_metadata['Pkg Height (in)'])
            ? (float) $this->raw_metadata['Pkg Height (in)']
            : null;
    }

    public function getInvoiceNumberAttribute(): ?string
    {
        return $this->raw_metadata['Invoice number'] ?? null;
    }

    public function getRecipientCompanyAttribute(): ?string
    {
        return $this->raw_metadata['Recipient company'] ?? null;
    }

    public function getShipperCompanyAttribute(): ?string
    {
        return $this->raw_metadata['Shipper company'] ?? null;
    }
}
