<?php

namespace Database\Seeders;

use App\Models\FedexLabel;
use Illuminate\Database\Seeder;

class FedexLabelsSeeder extends Seeder
{
    public function run(): void
    {
        FedexLabel::factory()
            ->hasEstimates(3)
            ->count(10)
            ->create();
    }
}
