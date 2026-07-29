<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'TRACTO', 'name' => 'Tractocamión', 'description' => 'Unidad tractora de carga pesada'],
            ['code' => 'REMOLQUE', 'name' => 'Remolque', 'description' => 'Remolque o semirremolque'],
            ['code' => 'PLATAFORMA', 'name' => 'Plataforma', 'description' => 'Plataforma de carga'],
            ['code' => 'TOLVA', 'name' => 'Tolva', 'description' => 'Tolva o volteo'],
            ['code' => 'CAJA', 'name' => 'Caja seca', 'description' => 'Caja seca / van'],
        ];

        foreach ($types as $type) {
            VehicleType::query()->updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'is_active' => true,
                ]
            );
        }

        [$min, $max] = [Vehicle::DEFAULT_YEAR_MIN, (int) now()->year + 1];

        Setting::query()->updateOrCreate(
            ['key' => Vehicle::YEAR_MIN_SETTING_KEY],
            [
                'value' => (string) $min,
                'type' => 'integer',
                'group' => 'vehicles',
                'description' => 'Año mínimo permitido al registrar unidades',
            ]
        );

        Setting::query()->updateOrCreate(
            ['key' => Vehicle::YEAR_MAX_SETTING_KEY],
            [
                'value' => (string) $max,
                'type' => 'integer',
                'group' => 'vehicles',
                'description' => 'Año máximo permitido al registrar unidades',
            ]
        );
    }
}
