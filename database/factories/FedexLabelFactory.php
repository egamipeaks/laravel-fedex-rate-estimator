<?php

namespace Database\Factories;

use App\Enums\FedexLabelStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class FedexLabelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tracking_number' => strtoupper($this->faker->bothify('##########')),
            'status' => $this->faker->randomElement(
                collect(FedexLabelStatus::cases())->pluck('value')->toArray()
            ),
            'residential' => $this->faker->boolean(),
            'raw_metadata' => [
                'Pkg Wt (Lbs)' => $this->faker->randomFloat(2, 1, 50),
                'Pkg Length (in)' => 10,
                'Pkg Width (in)' => 5,
                'Pkg Height (in)' => 4,
                'Recipient city' => $this->faker->city(),
                'Recipient state' => $this->faker->stateAbbr(),
                'Recipient postal' => $this->faker->postcode(),
                'Recipient company' => $this->faker->company(),
                'Shipper city' => $this->faker->city(),
                'Shipper state' => $this->faker->stateAbbr(),
                'Shipper postal' => $this->faker->postcode(),
                'Shipper company' => $this->faker->company(),
                'Service type' => $this->faker->randomElement([
                    'FEDEX_GROUND',
                    'FEDEX_2_DAY',
                    'FEDEX_EXPRESS_SAVER',
                    'FEDEX_STANDARD_OVERNIGHT',
                    'FEDEX_1_DAY_FREIGHT',
                ]),
                'Invoice number' => strtoupper($this->faker->bothify('??#??#??#')),
            ],
            'tracking_metadata' => null,
            'reference_number' => strtoupper($this->faker->bothify('??#??#??#')),
            'purchase_order' => strtoupper($this->faker->bothify('??#??#??#')),
        ];
    }
}
