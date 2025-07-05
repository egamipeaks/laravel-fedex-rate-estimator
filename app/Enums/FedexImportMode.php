<?php

namespace App\Enums;

enum FedexImportMode
{
    case MANUAL;
    case SCHEDULED;

    public function shouldSendNotification(): bool
    {
        return $this === self::MANUAL;
    }

    public function shouldSendEmail(): bool
    {
        return $this === self::SCHEDULED;
    }
}
