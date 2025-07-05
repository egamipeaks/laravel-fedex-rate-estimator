<?php

namespace App\Console\Commands;

use App\Mail\FedexImportSummaryTableMail;
use App\Models\FedexLabel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SendTestFedexSummaryEmailCommand extends Command
{
    protected $signature = 'fedex:test-email {--tracking-numbers= : CSV of tracking numbers}';

    protected $description = 'Send a test FedEx summary email with dummy data';

    public function handle(): int
    {
        $trackingNumbers = $this->option('tracking-numbers');

        if ($trackingNumbers) {
            $numbers = collect(explode(',', $trackingNumbers))->map(fn ($n) => trim($n));
            $newLabels = FedexLabel::whereIn('tracking_number', $numbers)->get();
            $updatedLabels = collect();
        } else {
            $newLabels = FedexLabel::inRandomOrder()->take(2)->get();
            $updatedLabels = FedexLabel::inRandomOrder()->take(2)->get();
        }

        if ($newLabels->isEmpty() && $updatedLabels->isEmpty()) {
            $this->error('No FedEx labels found in the database.');

            return CommandAlias::FAILURE;
        }

        Mail::to(config('services.fedex.reports.email', 'your-email@example.com'))
            ->send(new FedexImportSummaryTableMail($newLabels, $updatedLabels));

        $this->info('Test email sent successfully.');

        return CommandAlias::SUCCESS;
    }
}
