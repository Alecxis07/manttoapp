<?php

namespace Tests\Unit\Models;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditableTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_auditable_model_writes_activity_log(): void
    {
        $setting = Setting::query()->create([
            'key' => 'tax.iva_rate',
            'value' => '16.00',
            'type' => 'decimal',
            'group' => 'tax',
        ]);

        $log = ActivityLog::query()->where('action', 'created')->first();

        $this->assertNotNull($log);
        $this->assertSame(Setting::class, $log->subject_type);
        $this->assertSame($setting->id, $log->subject_id);
        $this->assertSame('16.00', $log->properties['after']['value'] ?? null);
    }

    public function test_updating_an_auditable_model_writes_before_and_after(): void
    {
        $setting = Setting::query()->create([
            'key' => 'tax.iva_rate',
            'value' => '16.00',
            'type' => 'decimal',
            'group' => 'tax',
        ]);

        ActivityLog::query()->delete();

        $setting->update(['value' => '8.00']);

        $log = ActivityLog::query()->where('action', 'updated')->first();

        $this->assertNotNull($log);
        $this->assertSame('16.00', $log->properties['before']['value'] ?? null);
        $this->assertSame('8.00', $log->properties['after']['value'] ?? null);
    }
}
