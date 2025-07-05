<?php

namespace App\Jobs;

use App\Enums\FedexImportMode;
use App\FedEx\Services\FedexCsvImporter;
use App\FedEx\Services\FedexFileDownloader;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RefreshFedexLabelsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const TIMEOUT = 900; // 15 minutes

    const CACHE_KEY = 'refresh-fedex-running';

    public $uniqueFor = 900; // 15 minutes

    public function uniqueId(): string
    {
        return 'refresh-fedex-labels';
    }

    public function __construct(public FedexImportMode $mode)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $lock = Cache::lock(self::CACHE_KEY, self::TIMEOUT);

        if (! $lock->get()) {
            return;
        }

        try {
            app(FedexFileDownloader::class)->run();

            $context = app(FedexCsvImporter::class)->run(
                $this->mode->shouldSendEmail()
            );

            $message = "FedEx import completed successfully. Imported {$context->newLabels->count()} new records, updated {$context->updatedLabels->count()} records.";

            Log::info($message);

            if ($this->mode->shouldSendNotification()) {
                Notification::make()
                    ->title('FedEx Labels Refreshed')
                    ->body($message)
                    ->success()
                    ->sendToDatabase(User::all());
            }

        } catch (\Throwable $e) {
            Log::error('FedEx import failed:', ['error' => $e->getMessage()]);

            if ($this->mode->shouldSendNotification()) {
                Notification::make()
                    ->title('FedEx Import Failed')
                    ->body(Str::limit($e->getMessage(), 200))
                    ->danger()
                    ->sendToDatabase(User::all());
            }
        } finally {
            $lock->release();
        }
    }
}
