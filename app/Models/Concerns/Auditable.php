<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            $model->writeActivityLog('created', null, $model->auditAttributes());
        });

        static::updated(function (Model $model): void {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if ($changes === []) {
                return;
            }

            $before = [];
            $after = [];

            foreach (array_keys($changes) as $attribute) {
                $before[$attribute] = $model->getOriginal($attribute);
                $after[$attribute] = $model->getAttribute($attribute);
            }

            $model->writeActivityLog('updated', $before, $after);
        });

        static::deleting(function (Model $model): void {
            $model->writeActivityLog('deleted', $model->auditAttributes(), null);
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function auditAttributes(): array
    {
        $hidden = property_exists($this, 'auditHidden')
            ? $this->auditHidden
            : ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

        return collect($this->attributesToArray())
            ->except($hidden)
            ->all();
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    protected function writeActivityLog(string $action, ?array $before, ?array $after): void
    {
        ActivityLog::query()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $this->getMorphClass(),
            'subject_id' => $this->getKey(),
            'properties' => array_filter([
                'before' => $before,
                'after' => $after,
            ], fn (?array $value): bool => $value !== null),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
