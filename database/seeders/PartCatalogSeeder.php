<?php

namespace Database\Seeders;

use App\Enums\PartCatalogType;
use App\Models\PartCatalog;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class PartCatalogSeeder extends Seeder
{
    /**
     * @var list<array{code: string, description: string, type: PartCatalogType, base_price: float, unit_of_measure: string, category: ?string}>
     */
    private const FIXED_PARTS = [
        [
            'code' => 'PRT-FILT-ACE',
            'description' => 'Filtro de aceite',
            'type' => PartCatalogType::Part,
            'base_price' => 180.00,
            'unit_of_measure' => 'pieza',
            'category' => 'PREV',
        ],
        [
            'code' => 'PRT-FILT-AIR',
            'description' => 'Filtro de aire',
            'type' => PartCatalogType::Part,
            'base_price' => 320.00,
            'unit_of_measure' => 'pieza',
            'category' => 'PREV',
        ],
        [
            'code' => 'PRT-FILT-COMB',
            'description' => 'Filtro de combustible',
            'type' => PartCatalogType::Part,
            'base_price' => 450.00,
            'unit_of_measure' => 'pieza',
            'category' => 'MOTOR',
        ],
        [
            'code' => 'PRT-ACEITE-15W40',
            'description' => 'Aceite 15W-40 diésel (litro)',
            'type' => PartCatalogType::Part,
            'base_price' => 95.00,
            'unit_of_measure' => 'litro',
            'category' => 'PREV',
        ],
        [
            'code' => 'PRT-ACEITE-5W30',
            'description' => 'Aceite 5W-30 sintético (litro)',
            'type' => PartCatalogType::Part,
            'base_price' => 145.00,
            'unit_of_measure' => 'litro',
            'category' => 'PREV',
        ],
        [
            'code' => 'PRT-BALATA-DEL',
            'description' => 'Juego de balatas delanteras',
            'type' => PartCatalogType::Part,
            'base_price' => 1250.00,
            'unit_of_measure' => 'kit',
            'category' => 'FRENOS',
        ],
        [
            'code' => 'PRT-BALATA-TRA',
            'description' => 'Juego de balatas traseras',
            'type' => PartCatalogType::Part,
            'base_price' => 980.00,
            'unit_of_measure' => 'kit',
            'category' => 'FRENOS',
        ],
        [
            'code' => 'PRT-BUJIA',
            'description' => 'Bujía de encendido',
            'type' => PartCatalogType::Part,
            'base_price' => 85.00,
            'unit_of_measure' => 'pieza',
            'category' => 'MOTOR',
        ],
        [
            'code' => 'PRT-BAT-12V',
            'description' => 'Batería 12V alta capacidad',
            'type' => PartCatalogType::Part,
            'base_price' => 2800.00,
            'unit_of_measure' => 'pieza',
            'category' => 'ELEC',
        ],
        [
            'code' => 'PRT-BANDAS',
            'description' => 'Banda de distribución',
            'type' => PartCatalogType::Part,
            'base_price' => 1650.00,
            'unit_of_measure' => 'pieza',
            'category' => 'MOTOR',
        ],
        [
            'code' => 'PRT-AMORT',
            'description' => 'Amortiguador pesado',
            'type' => PartCatalogType::Part,
            'base_price' => 2100.00,
            'unit_of_measure' => 'pieza',
            'category' => 'SUSP',
        ],
        [
            'code' => 'PRT-DISCO-FRENO',
            'description' => 'Disco de freno',
            'type' => PartCatalogType::Part,
            'base_price' => 890.00,
            'unit_of_measure' => 'pieza',
            'category' => 'FRENOS',
        ],
        [
            'code' => 'PRT-LLANTA-295',
            'description' => 'Llanta 295/75R22.5',
            'type' => PartCatalogType::Part,
            'base_price' => 4200.00,
            'unit_of_measure' => 'pieza',
            'category' => 'LLANTAS',
        ],
        [
            'code' => 'PRT-EMBRAGUE-KIT',
            'description' => 'Kit de embrague completo',
            'type' => PartCatalogType::Part,
            'base_price' => 6500.00,
            'unit_of_measure' => 'kit',
            'category' => 'TRANS',
        ],
        [
            'code' => 'PRT-MO-DIAG',
            'description' => 'Mano de obra diagnóstico',
            'type' => PartCatalogType::Labor,
            'base_price' => 450.00,
            'unit_of_measure' => 'hora',
            'category' => null,
        ],
    ];

    public function run(): void
    {
        $categoriesByCode = ServiceCategory::query()
            ->get()
            ->keyBy('code');

        foreach (self::FIXED_PARTS as $part) {
            $categoryId = null;

            if ($part['category'] !== null) {
                $category = $categoriesByCode->get($part['category']);
                $categoryId = $category?->id;
            }

            PartCatalog::query()->updateOrCreate(
                ['code' => $part['code']],
                [
                    'description' => $part['description'],
                    'type' => $part['type'],
                    'service_category_id' => $categoryId,
                    'base_price' => $part['base_price'],
                    'unit_of_measure' => $part['unit_of_measure'],
                    'is_active' => true,
                ]
            );
        }

        $remaining = 100 - count(self::FIXED_PARTS);

        if ($remaining > 0) {
            PartCatalog::factory()
                ->count($remaining)
                ->create();
        }
    }
}
