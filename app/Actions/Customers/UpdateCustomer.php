<?php

namespace App\Actions\Customers;

use App\DTOs\CustomerData;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateCustomer
{
    public function handle(Customer $customer, CustomerData $data, User $actor): Customer
    {
        return DB::transaction(function () use ($customer, $data, $actor): Customer {
            $customer->update([
                'type' => $data->type,
                'name' => $data->name,
                'trade_name' => $data->tradeName,
                'phone' => $data->phone,
                'email' => $data->email,
                'status' => $data->status,
                'updated_by' => $actor->id,
            ]);

            $this->syncFiscalProfiles($customer, $data->fiscalProfiles);

            return $customer->fresh(['fiscalProfiles']);
        });
    }

    /**
     * @param  list<array{
     *     id?: int|null,
     *     legal_name: string,
     *     rfc: string,
     *     tax_regime_code: string,
     *     cfdi_use_code: string,
     *     postal_code: string,
     *     email?: string|null,
     *     is_default: bool
     * }>  $profiles
     */
    protected function syncFiscalProfiles(Customer $customer, array $profiles): void
    {
        $keptIds = [];

        if ($profiles !== []) {
            $hasDefault = collect($profiles)->contains(fn (array $profile): bool => (bool) ($profile['is_default'] ?? false));

            foreach ($profiles as $index => $profile) {
                $isDefault = $hasDefault
                    ? (bool) ($profile['is_default'] ?? false)
                    : $index === 0;

                $attributes = [
                    'legal_name' => $profile['legal_name'],
                    'rfc' => strtoupper($profile['rfc']),
                    'tax_regime_code' => $profile['tax_regime_code'],
                    'cfdi_use_code' => $profile['cfdi_use_code'],
                    'postal_code' => $profile['postal_code'],
                    'email' => isset($profile['email']) && $profile['email'] !== ''
                        ? strtolower($profile['email'])
                        : null,
                    'is_default' => $isDefault,
                ];

                if (! empty($profile['id'])) {
                    $existing = $customer->fiscalProfiles()->whereKey($profile['id'])->first();

                    if ($existing !== null) {
                        $existing->update($attributes);
                        $keptIds[] = $existing->id;

                        continue;
                    }
                }

                $created = $customer->fiscalProfiles()->create($attributes);
                $keptIds[] = $created->id;
            }
        }

        $customer->fiscalProfiles()
            ->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))
            ->when($keptIds === [], fn ($query) => $query)
            ->delete();

        if ($customer->fiscalProfiles()->exists()) {
            $defaults = $customer->fiscalProfiles()->where('is_default', true)->count();

            if ($defaults === 0) {
                $customer->fiscalProfiles()->orderBy('id')->limit(1)->update(['is_default' => true]);
            } elseif ($defaults > 1) {
                $keepId = $customer->fiscalProfiles()->where('is_default', true)->orderBy('id')->value('id');
                $customer->fiscalProfiles()
                    ->where('is_default', true)
                    ->where('id', '!=', $keepId)
                    ->update(['is_default' => false]);
            }
        }
    }
}
