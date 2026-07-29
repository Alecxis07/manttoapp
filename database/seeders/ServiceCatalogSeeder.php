<?php

namespace Database\Seeders;

use App\Enums\ServiceCatalogType;
use App\Models\ServiceCatalog;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    /**
     * @var list<array{code: string, description: string, category: string, type: ServiceCatalogType, base_price: float, unit_of_measure: string, estimated_minutes: int}>
     */
    private const FIXED_SERVICES = [
        [
            'code' => 'SRV-ACEITE',
            'description' => 'Cambio de aceite y filtro',
            'category' => 'PREV',
            'type' => ServiceCatalogType::Preventive,
            'base_price' => 850.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 45,
        ],
        [
            'code' => 'SRV-AFIN-MAY',
            'description' => 'Afinación mayor',
            'category' => 'PREV',
            'type' => ServiceCatalogType::Preventive,
            'base_price' => 4500.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 240,
        ],
        [
            'code' => 'SRV-AFIN-MEN',
            'description' => 'Afinación menor',
            'category' => 'PREV',
            'type' => ServiceCatalogType::Preventive,
            'base_price' => 2200.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 120,
        ],
        [
            'code' => 'SRV-DIAG-ELEC',
            'description' => 'Diagnóstico electrónico',
            'category' => 'ELEC',
            'type' => ServiceCatalogType::Corrective,
            'base_price' => 650.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 60,
        ],
        [
            'code' => 'SRV-FRENOS',
            'description' => 'Revisión y ajuste de frenos',
            'category' => 'FRENOS',
            'type' => ServiceCatalogType::Corrective,
            'base_price' => 1200.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 90,
        ],
        [
            'code' => 'SRV-BALATAS',
            'description' => 'Cambio de balatas',
            'category' => 'FRENOS',
            'type' => ServiceCatalogType::Corrective,
            'base_price' => 1800.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 120,
        ],
        [
            'code' => 'SRV-SUSP',
            'description' => 'Revisión de suspensión',
            'category' => 'SUSP',
            'type' => ServiceCatalogType::Corrective,
            'base_price' => 950.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 75,
        ],
        [
            'code' => 'SRV-ALINEA',
            'description' => 'Alineación y balanceo',
            'category' => 'SUSP',
            'type' => ServiceCatalogType::Preventive,
            'base_price' => 780.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 60,
        ],
        [
            'code' => 'SRV-TRANS',
            'description' => 'Servicio de transmisión',
            'category' => 'TRANS',
            'type' => ServiceCatalogType::Corrective,
            'base_price' => 3500.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 180,
        ],
        [
            'code' => 'SRV-EMBRAGUE',
            'description' => 'Cambio de embrague',
            'category' => 'TRANS',
            'type' => ServiceCatalogType::Corrective,
            'base_price' => 8500.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 360,
        ],
        [
            'code' => 'SRV-MOTOR-REV',
            'description' => 'Revisión general de motor',
            'category' => 'MOTOR',
            'type' => ServiceCatalogType::Corrective,
            'base_price' => 1500.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 90,
        ],
        [
            'code' => 'SRV-INYECT',
            'description' => 'Limpieza de inyectores',
            'category' => 'MOTOR',
            'type' => ServiceCatalogType::Preventive,
            'base_price' => 2100.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 120,
        ],
        [
            'code' => 'SRV-BATERIA',
            'description' => 'Prueba y cambio de batería',
            'category' => 'ELEC',
            'type' => ServiceCatalogType::Corrective,
            'base_price' => 450.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 30,
        ],
        [
            'code' => 'SRV-LLANTAS',
            'description' => 'Rotación de llantas',
            'category' => 'LLANTAS',
            'type' => ServiceCatalogType::Preventive,
            'base_price' => 350.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 40,
        ],
        [
            'code' => 'SRV-CARRO',
            'description' => 'Reparación de carrocería menor',
            'category' => 'CARRO',
            'type' => ServiceCatalogType::Corrective,
            'base_price' => 2800.00,
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 240,
        ],
    ];

    public function run(): void
    {
        $categoriesByCode = ServiceCategory::query()
            ->get()
            ->keyBy('code');

        foreach (self::FIXED_SERVICES as $service) {
            $category = $categoriesByCode->get($service['category']);

            if ($category === null) {
                continue;
            }

            ServiceCatalog::query()->updateOrCreate(
                ['code' => $service['code']],
                [
                    'description' => $service['description'],
                    'service_category_id' => $category->id,
                    'type' => $service['type'],
                    'base_price' => $service['base_price'],
                    'unit_of_measure' => $service['unit_of_measure'],
                    'estimated_minutes' => $service['estimated_minutes'],
                    'is_active' => true,
                ]
            );
        }

        $categoryIds = $categoriesByCode->pluck('id')->all();

        if ($categoryIds === []) {
            return;
        }

        $remaining = 50 - count(self::FIXED_SERVICES);

        if ($remaining > 0) {
            ServiceCatalog::factory()
                ->count($remaining)
                ->state(fn (): array => [
                    'service_category_id' => fake()->randomElement($categoryIds),
                ])
                ->create();
        }
    }
}
