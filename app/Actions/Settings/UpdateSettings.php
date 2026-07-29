<?php

namespace App\Actions\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class UpdateSettings
{
    /**
     * @param  array<string, array{value: string, type?: string, group?: string, description?: string}>  $settings
     */
    public function handle(array $settings): void
    {
        DB::transaction(function () use ($settings): void {
            foreach ($settings as $key => $payload) {
                Setting::query()->updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $payload['value'],
                        'type' => $payload['type'] ?? 'string',
                        'group' => $payload['group'] ?? null,
                        'description' => $payload['description'] ?? null,
                    ]
                );
            }
        });
    }
}
