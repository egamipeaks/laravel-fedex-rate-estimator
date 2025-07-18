<?php

namespace App\Enums;

enum FedexServiceType: string
{
    case FEDEX_GROUND = 'FEDEX_GROUND';
    case GROUND_HOME_DELIVERY = 'GROUND_HOME_DELIVERY';
    case PRIORITY_OVERNIGHT = 'PRIORITY_OVERNIGHT';
    case FEDEX_EXPRESS_SAVER = 'FEDEX_EXPRESS_SAVER';
    case FEDEX_2_DAY = 'FEDEX_2_DAY';
    case STANDARD_OVERNIGHT = 'STANDARD_OVERNIGHT';
}
