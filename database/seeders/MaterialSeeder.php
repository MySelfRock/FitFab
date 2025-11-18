<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            [
                'name' => 'MDF Branco 15mm',
                'type' => 'mdf',
                'thickness_mm' => 15.00,
                'sheet_width_mm' => 1830.00,
                'sheet_height_mm' => 2750.00,
                'price_per_sheet' => 180.00,
                'properties' => [
                    'color' => 'branco',
                    'finish' => 'liso',
                    'density' => 'media',
                ],
                'active' => true,
            ],
            [
                'name' => 'MDP Branco 15mm',
                'type' => 'mdp',
                'thickness_mm' => 15.00,
                'sheet_width_mm' => 1830.00,
                'sheet_height_mm' => 2750.00,
                'price_per_sheet' => 150.00,
                'properties' => [
                    'color' => 'branco',
                    'finish' => 'melamina',
                    'density' => 'media',
                ],
                'active' => true,
            ],
            [
                'name' => 'MDF 18mm Natural',
                'type' => 'mdf',
                'thickness_mm' => 18.00,
                'sheet_width_mm' => 1830.00,
                'sheet_height_mm' => 2750.00,
                'price_per_sheet' => 200.00,
                'properties' => [
                    'color' => 'natural',
                    'finish' => 'cru',
                    'density' => 'alta',
                ],
                'active' => true,
            ],
            [
                'name' => 'Compensado Naval 12mm',
                'type' => 'compensado',
                'thickness_mm' => 12.00,
                'sheet_width_mm' => 1220.00,
                'sheet_height_mm' => 2440.00,
                'price_per_sheet' => 220.00,
                'properties' => [
                    'color' => 'madeira',
                    'finish' => 'natural',
                    'density' => 'alta',
                    'water_resistant' => true,
                ],
                'active' => true,
            ],
        ];

        foreach ($materials as $material) {
            Material::create($material);
        }

        $this->command->info('Materials seeded successfully!');
    }
}
