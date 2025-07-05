<?php

use App\Enums\FedexLabelStatus;
use App\Models\FedexLabel;

it('returns true if the label is delivered', function () {
    $label = new FedexLabel(['status' => FedexLabelStatus::Delivered]);
    expect($label->isDelivered())->toBeTrue();
});

it('returns false if the label is not delivered', function () {
    $label = new FedexLabel(['status' => FedexLabelStatus::OnTheWay]);
    expect($label->isDelivered())->toBeFalse();
});

it('returns the correct status icon', function () {
    $label = new FedexLabel(['status' => FedexLabelStatus::OutForDelivery]);
    expect($label->status_icon)->toBe('🚚');
});

it('returns the correct tracking link', function () {
    $label = new FedexLabel(['tracking_number' => '123456789']);
    expect($label->tracking_link)->toBe('https://www.fedex.com/apps/fedextrack/?action=track&tracknumbers=123456789&clienttype=ivother');
});

it('handles null status gracefully', function () {
    $label = new FedexLabel(['status' => null]);
    expect($label->status_icon)->toBe('');
});

it('handles null tracking number gracefully', function () {
    $label = new FedexLabel(['tracking_number' => null]);
    expect($label->tracking_link)->toBe('https://www.fedex.com/apps/fedextrack/?action=track&tracknumbers=&clienttype=ivother');
});
