<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerFiscalProfile;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // ~30 companies (~60% of 50)
        Customer::factory()
            ->count(24)
            ->company()
            ->has(CustomerFiscalProfile::factory()->default(), 'fiscalProfiles')
            ->create();

        Customer::factory()
            ->count(3)
            ->company()
            ->inactive()
            ->has(CustomerFiscalProfile::factory()->default(), 'fiscalProfiles')
            ->create();

        Customer::factory()
            ->count(3)
            ->company()
            ->withoutEmail()
            ->has(CustomerFiscalProfile::factory()->default(), 'fiscalProfiles')
            ->create();

        // ~20 individuals
        Customer::factory()
            ->count(15)
            ->has(CustomerFiscalProfile::factory()->default(), 'fiscalProfiles')
            ->create();

        Customer::factory()
            ->count(2)
            ->inactive()
            ->has(CustomerFiscalProfile::factory()->default(), 'fiscalProfiles')
            ->create();

        Customer::factory()
            ->count(3)
            ->withoutEmail()
            ->has(CustomerFiscalProfile::factory()->default(), 'fiscalProfiles')
            ->create();

        // Some customers get an additional secondary fiscal profile
        Customer::query()
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->each(function (Customer $customer): void {
                CustomerFiscalProfile::factory()
                    ->secondary()
                    ->for($customer)
                    ->create();
            });
    }
}
