<?php

namespace App\FedEx\Services;

use Symfony\Component\Process\Process;

class FedexFileDownloader
{
    const TIMEOUT = 900; // 15 minutes

    public function run(): void
    {
        logger()->info('FedEx downloader started');

        $process = new Process(
            [
                config('app.node_path'),
                base_path('fedex-bot/fedex-login.js'),
            ],
            base_path(),
            [
                'DOWNLOAD_PATH' => storage_path('app/private/fedex'),
                'LOG_PATH' => storage_path('logs'),
                'FEDEX_USERNAME' => config('services.fedex.username'),
                'FEDEX_PASSWORD' => config('services.fedex.password'),
            ]
        );

        // Give it up to 15m if needed
        $process->setTimeout(self::TIMEOUT);
        $process->mustRun();

        logger()->info('FedEx downloader finished:', [
            'output' => $process->getOutput(),
        ]);
    }
}
