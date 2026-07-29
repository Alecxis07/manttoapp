<?php

namespace App\Http\Requests;

use App\DTOs\CustomerData;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Customer::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }

        if ($this->input('email') === '') {
            $this->merge(['email' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(CustomerType::class)],
            'name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'status' => ['required', Rule::enum(CustomerStatus::class)],
            'fiscal_profiles' => ['nullable', 'array'],
            'fiscal_profiles.*.legal_name' => ['required_with:fiscal_profiles', 'string', 'max:255'],
            'fiscal_profiles.*.rfc' => ['required_with:fiscal_profiles', 'string', 'max:13'],
            'fiscal_profiles.*.tax_regime_code' => ['required_with:fiscal_profiles', 'string', 'max:10'],
            'fiscal_profiles.*.cfdi_use_code' => ['required_with:fiscal_profiles', 'string', 'max:10'],
            'fiscal_profiles.*.postal_code' => ['required_with:fiscal_profiles', 'string', 'max:10'],
            'fiscal_profiles.*.email' => ['nullable', 'email', 'max:255'],
            'fiscal_profiles.*.is_default' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateUniqueEmail($validator);
            $this->validateCompanyRequiresTradeName($validator);
            $this->validateSingleDefaultFiscalProfile($validator);
        });
    }

    public function toDto(): CustomerData
    {
        /** @var array{
         *     type: string,
         *     name: string,
         *     trade_name?: string|null,
         *     phone?: string|null,
         *     email?: string|null,
         *     status: string,
         *     fiscal_profiles?: list<array{
         *         legal_name: string,
         *         rfc: string,
         *         tax_regime_code: string,
         *         cfdi_use_code: string,
         *         postal_code: string,
         *         email?: string|null,
         *         is_default?: bool
         *     }>
         * } $data
         */
        $data = $this->validated();

        $profiles = collect($data['fiscal_profiles'] ?? [])
            ->map(fn (array $profile): array => [
                'legal_name' => $profile['legal_name'],
                'rfc' => $profile['rfc'],
                'tax_regime_code' => $profile['tax_regime_code'],
                'cfdi_use_code' => $profile['cfdi_use_code'],
                'postal_code' => $profile['postal_code'],
                'email' => $profile['email'] ?? null,
                'is_default' => (bool) ($profile['is_default'] ?? false),
            ])
            ->values()
            ->all();

        return new CustomerData(
            type: CustomerType::from($data['type']),
            name: $data['name'],
            tradeName: $data['trade_name'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            status: CustomerStatus::from($data['status']),
            fiscalProfiles: $profiles,
        );
    }

    protected function validateUniqueEmail(Validator $validator): void
    {
        $email = $this->input('email');

        if (! is_string($email) || $email === '') {
            return;
        }

        $exists = Customer::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->exists();

        if ($exists) {
            $validator->errors()->add('email', __('El email del cliente ya está registrado.'));
        }
    }

    protected function validateCompanyRequiresTradeName(Validator $validator): void
    {
        if ($this->input('type') === CustomerType::Company->value && blank($this->input('trade_name'))) {
            $validator->errors()->add('trade_name', __('El nombre comercial es obligatorio para persona moral.'));
        }
    }

    protected function validateSingleDefaultFiscalProfile(Validator $validator): void
    {
        $profiles = $this->input('fiscal_profiles', []);

        if (! is_array($profiles) || $profiles === []) {
            return;
        }

        $defaults = collect($profiles)->filter(fn ($profile): bool => (bool) data_get($profile, 'is_default'))->count();

        if ($defaults > 1) {
            $validator->errors()->add('fiscal_profiles', __('Solo un perfil fiscal puede ser predeterminado.'));
        }
    }
}
