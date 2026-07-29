<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    /**
     * @var list<array{code: string, name: string, description: string}>
     */
    private const CATEGORIES = [
        [
            'code' => 'PREV',
            'name' => 'Mantenimiento Preventivo',
            'description' => 'Servicios programados de mantenimiento preventivo',
        ],
        [
            'code' => 'MOTOR',
            'name' => 'Motor',
            'description' => 'Diagnóstico y reparación de motor',
        ],
        [
            'code' => 'TRANS',
            'name' => 'Transmisión',
            'description' => 'Caja de velocidades, embrague y diferencial',
        ],
        [
            'code' => 'FRENOS',
            'name' => 'Frenos',
            'description' => 'Sistema de frenos y componentes asociados',
        ],
        [
            'code' => 'SUSP',
            'name' => 'Suspensión y Dirección',
            'description' => 'Suspensión, dirección y alineación',
        ],
        [
            'code' => 'ELEC',
            'name' => 'Sistema Eléctrico',
            'description' => 'Sistema eléctrico, baterías y arranque',
        ],
        [
            'code' => 'LLANTAS',
            'name' => 'Llantas y Rines',
            'description' => 'Llantas, rines y balanceo',
        ],
        [
            'code' => 'CARRO',
            'name' => 'Carrocería',
            'description' => 'Carrocería, pintura y detalles exteriores',
        ],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            ServiceCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
