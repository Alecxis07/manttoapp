<?php

namespace Database\Seeders;

use App\Actions\Maintenance\UploadOrderAttachment;
use App\Models\Setting;
use App\Services\TotalsCalculator;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            VehicleTypeSeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
            ServiceCategorySeeder::class,
            ServiceCatalogSeeder::class,
            PartCatalogSeeder::class,
            VehicleSeeder::class,
        ]);

        Setting::query()->updateOrCreate(
            ['key' => TotalsCalculator::IVA_SETTING_KEY],
            [
                'value' => TotalsCalculator::DEFAULT_IVA_RATE,
                'type' => 'decimal',
                'group' => 'tax',
                'description' => 'Tasa de IVA (%) aplicable a nuevos documentos',
            ]
        );

        Setting::query()->updateOrCreate(
            ['key' => UploadOrderAttachment::MAX_SIZE_SETTING_KEY],
            [
                'value' => (string) UploadOrderAttachment::DEFAULT_MAX_SIZE_KB,
                'type' => 'integer',
                'group' => 'attachments',
                'description' => 'Tamaño máximo de adjuntos en KB',
            ]
        );

        Setting::query()->updateOrCreate(
            ['key' => UploadOrderAttachment::ALLOWED_MIMES_SETTING_KEY],
            [
                'value' => json_encode(UploadOrderAttachment::DEFAULT_ALLOWED_MIMES, JSON_THROW_ON_ERROR),
                'type' => 'json',
                'group' => 'attachments',
                'description' => 'Tipos MIME permitidos para adjuntos',
            ]
        );
    }
}
