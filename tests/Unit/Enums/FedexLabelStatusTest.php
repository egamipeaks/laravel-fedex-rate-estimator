<?php

namespace Tests\Enums;

use App\Enums\FedexLabelStatus;

test('icon method returns correct icons', function () {
    expect(FedexLabelStatus::Delivered->icon())->toBe('✅')
        ->and(FedexLabelStatus::OnTheWay->icon())->toBe('📦')
        ->and(FedexLabelStatus::LabelCreated->icon())->toBe('🟣')
        ->and(FedexLabelStatus::DeliveryUpdated->icon())->toBe('🔁')
        ->and(FedexLabelStatus::Cancelled->icon())->toBe('❌')
        ->and(FedexLabelStatus::DeliveryException->icon())->toBe('⚠️')
        ->and(FedexLabelStatus::OutForDelivery->icon())->toBe('🚚')
        ->and(FedexLabelStatus::AtOurFacility->icon())->toBe('🏢')
        ->and(FedexLabelStatus::WeHaveYourPackage->icon())->toBe('📦✅')
        ->and(FedexLabelStatus::ReturningToFacility->icon())->toBe('↩️')
        ->and(FedexLabelStatus::RunningLate->icon())->toBe('⏳')
        ->and(FedexLabelStatus::Unknown->icon())->toBe('❔');
});

test('fromString method maps strings to enum values', function () {
    expect(FedexLabelStatus::fromString('Delivered'))->toBe(FedexLabelStatus::Delivered)
        ->and(FedexLabelStatus::fromString('On the way'))->toBe(FedexLabelStatus::OnTheWay)
        ->and(FedexLabelStatus::fromString('Label created'))->toBe(FedexLabelStatus::LabelCreated)
        ->and(FedexLabelStatus::fromString('Delivery updated'))->toBe(FedexLabelStatus::DeliveryUpdated)
        ->and(FedexLabelStatus::fromString('Cancelled'))->toBe(FedexLabelStatus::Cancelled)
        ->and(FedexLabelStatus::fromString('Delivery exception'))->toBe(FedexLabelStatus::DeliveryException)
        ->and(FedexLabelStatus::fromString('Out for delivery'))->toBe(FedexLabelStatus::OutForDelivery)
        ->and(FedexLabelStatus::fromString('At our facility'))->toBe(FedexLabelStatus::AtOurFacility)
        ->and(FedexLabelStatus::fromString('We have your package'))->toBe(FedexLabelStatus::WeHaveYourPackage)
        ->and(FedexLabelStatus::fromString('Returning to FedEx facility'))->toBe(FedexLabelStatus::ReturningToFacility)
        ->and(FedexLabelStatus::fromString('Running late'))->toBe(FedexLabelStatus::RunningLate);
});

test('description method returns correct messages', function () {
    expect(FedexLabelStatus::Delivered->description())->toBe('Your package has been delivered. Thank you for using FedEx.')
        ->and(FedexLabelStatus::OnTheWay->description())->toBe('Your package is moving toward its final destination. This means it’s in a moving vehicle or at one of our facilities. There’s nothing you need to do at this point.')
        ->and(FedexLabelStatus::LabelCreated->description())->toBe('The shipper printed the shipping label and is preparing your package to be handed over to FedEx for delivery. There’s nothing you need to do at this point. We’ll update your shipment once we have it.')
        ->and(FedexLabelStatus::DeliveryUpdated->description())->toBe('A change was made to the delivery date or address. This was requested by the shipper/recipient or because of exceptional events (e.g. the package contains a prohibited item or exceeds weight/size limit). You can find more information about the change in the tracking status or in the tracking history.')
        ->and(FedexLabelStatus::Cancelled->description())->toBe('The delivery of your package has been cancelled.')
        ->and(FedexLabelStatus::DeliveryException->description())->toBe('An unexpected event is delaying the delivery of your package. Examples may include: customs delay, bad weather, strikes, recipient wasn’t home, or security restrictions. There’s nothing you need to do at this point.')
        ->and(FedexLabelStatus::OutForDelivery->description())->toBe('Your package was scanned and placed on a vehicle for delivery that day. Feel free to check the tracking page for an estimated delivery window.')
        ->and(FedexLabelStatus::AtOurFacility->description())->toBe('Your package is safe at a FedEx-approved location. There’s nothing you need to do. We’ll update the shipment status when it moves again.')
        ->and(FedexLabelStatus::WeHaveYourPackage->description())->toBe('Your package arrived at a FedEx-approved facility or was picked up by a FedEx courier. There’s nothing you need to do at this point.')
        ->and(FedexLabelStatus::ReturningToFacility->description())->toBe('The package is being sent back to a FedEx location after a delivery attempt. This can occur if the package is turned away by the recipient for reasons such as damage or inability to pay the delivery fee. The shipper will be contacted to decide next steps. There’s nothing needed from the recipient at this point.')
        ->and(FedexLabelStatus::RunningLate->description())->toBe('Your package is delayed and likely won’t arrive before its original estimated date or time. This can happen due to higher shipping volumes, dangerous weather, vehicle breakdowns, or local security restrictions. There’s nothing you need to do at this point. Rest assured that we’re working to deliver your package as soon as possible.')
        ->and(FedexLabelStatus::Unknown->description())->toBe('The status of your package is currently unknown. Please check back later for updates.');
});

test('label method returns correct labels', function () {
    expect(FedexLabelStatus::Delivered->label())->toBe('Delivered')
        ->and(FedexLabelStatus::OnTheWay->label())->toBe('In Transit')
        ->and(FedexLabelStatus::LabelCreated->label())->toBe('Label Created')
        ->and(FedexLabelStatus::DeliveryUpdated->label())->toBe('Updated')
        ->and(FedexLabelStatus::Cancelled->label())->toBe('Cancelled')
        ->and(FedexLabelStatus::DeliveryException->label())->toBe('Delivery Exception')
        ->and(FedexLabelStatus::OutForDelivery->label())->toBe('Out for Delivery')
        ->and(FedexLabelStatus::AtOurFacility->label())->toBe('At Facility')
        ->and(FedexLabelStatus::WeHaveYourPackage->label())->toBe('We Have Your Package')
        ->and(FedexLabelStatus::ReturningToFacility->label())->toBe('Returning to Facility')
        ->and(FedexLabelStatus::RunningLate->label())->toBe('Running Late')
        ->and(FedexLabelStatus::Unknown->label())->toBe('Unknown');
});
