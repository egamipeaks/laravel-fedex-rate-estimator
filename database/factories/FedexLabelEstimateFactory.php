<?php

namespace Database\Factories;

use App\Enums\FedexServiceType;
use App\Models\FedexLabel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FedexLabelEstimateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_type' => $this->faker->randomElement(
                collect(FedexServiceType::cases())->pluck('value')->toArray()
            ),
            'estimate' => $this->faker->randomNumber(),
            'origin' => [
                'postal_code' => $this->faker->postcode(),
                'city' => $this->faker->city(),
                'stateOrProvinceCode' => $this->faker->stateAbbr(),
                'residential' => $this->faker->boolean(),
            ],
            'destination' => [
                'postal_code' => $this->faker->postcode(),
                'city' => $this->faker->city(),
                'stateOrProvinceCode' => $this->faker->stateAbbr(),
                'residential' => $this->faker->boolean(),
            ],
            'package' => [
                'width' => $this->faker->randomFloat(2, 1, 50),
                'height' => $this->faker->randomFloat(2, 1, 50),
                'length' => $this->faker->randomFloat(2, 1, 50),
                'weight' => $this->faker->randomFloat(2, 1, 50),
                'weight_unit' => $this->faker->randomElement(['LB', 'KG']),
                'dimensions_unit' => $this->faker->randomElement(['IN', 'CM']),
            ],
            'raw_payload' => [
                'ratedShipmentDetails' => [
                    [
                        'ratedPackages' => [
                            [
                                'packageRateDetail' => [
                                    'baseCharge' => $this->faker->randomFloat(2, 1, 50),
                                    'surcharges' => [
                                        [
                                            'description' => 'Fuel Surcharge',
                                            'type' => 'FUEL',
                                            'amount' => $this->faker->randomFloat(2, 1, 50),
                                        ],
                                        [
                                            'description' => 'Fuel Surcharge',
                                            'type' => 'FUEL',
                                            'amount' => $this->faker->randomFloat(2, 1, 50),
                                        ],
                                    ],
                                    'freightDiscounts' => [
                                        [
                                            'description' => 'Volume',
                                            'type' => 'VOLUME',
                                            'amount' => $this->faker->randomFloat(2, 1, 50),
                                        ],
                                        [
                                            'description' => 'Volume',
                                            'type' => 'VOLUME',
                                            'amount' => $this->faker->randomFloat(2, 1, 50),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'totalNetCharge' => $this->faker->randomFloat(2, 1, 50),
                    ],
                ],
            ],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'fedex_label_id' => FedexLabel::factory(),
        ];
    }
}
