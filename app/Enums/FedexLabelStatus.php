<?php

namespace App\Enums;

enum FedexLabelStatus: string
{
    case Delivered = 'Delivered';
    case OnTheWay = 'On the way';
    case LabelCreated = 'Label created';
    case DeliveryUpdated = 'Delivery updated';
    case Cancelled = 'Cancelled';
    case Unknown = 'Unknown';
    case DeliveryException = 'Delivery exception';
    case OutForDelivery = 'Out for delivery';
    case AtOurFacility = 'At our facility';
    case WeHaveYourPackage = 'We have your package';
    case ReturningToFacility = 'Returning to FedEx facility';
    case RunningLate = 'Running late';

    public function icon(): string
    {
        return match ($this) {
            self::Delivered => '✅',
            self::OnTheWay => '📦',
            self::LabelCreated => '🟣',
            self::DeliveryUpdated => '🔁',
            self::Cancelled => '❌',
            self::DeliveryException => '⚠️',
            self::OutForDelivery => '🚚',
            self::AtOurFacility => '🏢',
            self::WeHaveYourPackage => '📦✅',
            self::ReturningToFacility => '↩️',
            self::RunningLate => '⏳',
            self::Unknown => '❔',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Delivered => 'Delivered',
            self::OnTheWay => 'In Transit',
            self::LabelCreated => 'Label Created',
            self::DeliveryUpdated => 'Updated',
            self::Cancelled => 'Cancelled',
            self::DeliveryException => 'Delivery Exception',
            self::OutForDelivery => 'Out for Delivery',
            self::AtOurFacility => 'At Facility',
            self::WeHaveYourPackage => 'We Have Your Package',
            self::ReturningToFacility => 'Returning to Facility',
            self::RunningLate => 'Running Late',
            self::Unknown => 'Unknown',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Delivered => 'Your package has been delivered. Thank you for using FedEx.',
            self::OnTheWay => 'Your package is moving toward its final destination. This means it’s in a moving vehicle or at one of our facilities. There’s nothing you need to do at this point.',
            self::LabelCreated => 'The shipper printed the shipping label and is preparing your package to be handed over to FedEx for delivery. There’s nothing you need to do at this point. We’ll update your shipment once we have it.',
            self::DeliveryUpdated => 'A change was made to the delivery date or address. This was requested by the shipper/recipient or because of exceptional events (e.g. the package contains a prohibited item or exceeds weight/size limit). You can find more information about the change in the tracking status or in the tracking history.',
            self::Cancelled => 'The delivery of your package has been cancelled.',
            self::DeliveryException => 'An unexpected event is delaying the delivery of your package. Examples may include: customs delay, bad weather, strikes, recipient wasn’t home, or security restrictions. There’s nothing you need to do at this point.',
            self::OutForDelivery => 'Your package was scanned and placed on a vehicle for delivery that day. Feel free to check the tracking page for an estimated delivery window.',
            self::AtOurFacility => 'Your package is safe at a FedEx-approved location. There’s nothing you need to do. We’ll update the shipment status when it moves again.',
            self::WeHaveYourPackage => 'Your package arrived at a FedEx-approved facility or was picked up by a FedEx courier. There’s nothing you need to do at this point.',
            self::ReturningToFacility => 'The package is being sent back to a FedEx location after a delivery attempt. This can occur if the package is turned away by the recipient for reasons such as damage or inability to pay the delivery fee. The shipper will be contacted to decide next steps. There’s nothing needed from the recipient at this point.',
            self::RunningLate => 'Your package is delayed and likely won’t arrive before its original estimated date or time. This can happen due to higher shipping volumes, dangerous weather, vehicle breakdowns, or local security restrictions. There’s nothing you need to do at this point. Rest assured that we’re working to deliver your package as soon as possible.',
            self::Unknown => 'The status of your package is currently unknown. Please check back later for updates.',
        };
    }

    public static function fromString(string $value): self
    {
        return match (trim(strtolower($value))) {
            'delivered' => self::Delivered,
            'on the way' => self::OnTheWay,
            'label created' => self::LabelCreated,
            'delivery updated' => self::DeliveryUpdated,
            'cancelled' => self::Cancelled,
            'delivery exception' => self::DeliveryException,
            'out for delivery' => self::OutForDelivery,
            'at our facility' => self::AtOurFacility,
            'we have your package' => self::WeHaveYourPackage,
            'returning to fedex facility' => self::ReturningToFacility,
            'running late' => self::RunningLate,
            default => self::Unknown,
        };
    }
}
