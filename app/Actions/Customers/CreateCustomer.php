<?php

namespace App\Actions\Customers;

use App\DTOs\CustomerData;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateCustomer
{
    public function handle(CustomerData $data, User $actor): Customer
    {
        return DB::transaction(function () use ($data, $actor): Customer {
            $customer = Customer::query()->create([
                'type' => $data->type,
                'name' => $data->name,
                'trade_name' => $data->tradeName,
                'phone' => $data->phone,
                'email' => $data->email,
                'status' => $data->status,
                'created_by' => $actor->id,
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
        if ($profiles === []) {
            return;
        }

        $hasDefault = collect($profiles)->contains(fn (array $profile): bool => (bool) ($profile['is_default'] ?? false));

        foreach ($profiles as $index => $profile) {
            $isDefault = $hasDefault
                ? (bool) ($profile['is_default'] ?? false)
                : $index === 0;

            $customer->fiscalProfiles()->create([
                'legal_name' => $profile['legal_name'],
                'rfc' => strtoupper($profile['rfc']),
                'tax_regime_code' => $profile['tax_regime_code'],
                'cfdi_use_code' => $profile['cfdi_use_code'],
                'postal_code' => $profile['postal_code'],
                'email' => isset($profile['email']) && $profile['email'] !== ''
                    ? strtolower($profile['email'])
                    : null,
                'is_default' => $isDefault,
            ]);
        }

        // RN-CLI-003: ensure exactly one default when profiles exist.
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
