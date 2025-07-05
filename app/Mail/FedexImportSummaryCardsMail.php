<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class FedexImportSummaryCardsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $newLabels,
        public Collection $updatedLabels,
    ) {}

    public function build(): self
    {
        return $this->view('emails.fedex.summary-html')
            ->subject('FedEx Shipment Summary')
            ->with([
                'newLabels' => $this->newLabels,
                'updatedLabels' => $this->updatedLabels,
            ]);
    }
}
