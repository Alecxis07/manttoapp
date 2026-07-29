<?php

namespace App\Http\Requests;

use App\Actions\Maintenance\UploadOrderAttachment;
use App\Services\TotalsCalculator;
use App\Support\FolioGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.*') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $mimes = $this->input('attachments_allowed_mimes');

        if (is_string($mimes)) {
            $normalized = collect(preg_split('/[\s,]+/', $mimes) ?: [])
                ->map(fn (string $mime): string => strtolower(trim($mime)))
                ->filter()
                ->values()
                ->all();

            $this->merge([
                'attachments_allowed_mimes' => $normalized,
            ]);
        }

        if ($this->has('sequences') && is_array($this->input('sequences'))) {
            $sequences = collect($this->input('sequences'))
                ->map(function (mixed $row): array {
                    $row = is_array($row) ? $row : [];

                    return [
                        'document_type' => (string) ($row['document_type'] ?? ''),
                        'prefix' => strtoupper(trim((string) ($row['prefix'] ?? ''))),
                        'padding' => (int) ($row['padding'] ?? 5),
                        'reset_counter' => filter_var($row['reset_counter'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    ];
                })
                ->values()
                ->all();

            $this->merge(['sequences' => $sequences]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tax_iva_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'attachments_max_size_kb' => ['required', 'integer', 'min:1', 'max:51200'],
            'attachments_allowed_mimes' => ['required', 'array', 'min:1'],
            'attachments_allowed_mimes.*' => ['required', 'string', 'max:100'],
            'sequences' => ['required', 'array', 'min:1'],
            'sequences.*.document_type' => ['required', 'string', Rule::in(array_keys(FolioGenerator::DEFAULT_PREFIXES))],
            'sequences.*.prefix' => ['required', 'string', 'max:16', 'regex:/^[A-Z0-9]+$/'],
            'sequences.*.padding' => ['required', 'integer', 'min:1', 'max:10'],
            'sequences.*.reset_counter' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tax_iva_rate' => 'tasa de IVA',
            'attachments_max_size_kb' => 'tamaño máximo de archivo',
            'attachments_allowed_mimes' => 'tipos MIME permitidos',
            'sequences.*.prefix' => 'prefijo de folio',
            'sequences.*.padding' => 'longitud del correlativo',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $types = collect($this->input('sequences', []))->pluck('document_type');

            foreach (array_keys(FolioGenerator::DEFAULT_PREFIXES) as $requiredType) {
                if (! $types->contains($requiredType)) {
                    $validator->errors()->add(
                        'sequences',
                        __('Debe configurar la secuencia :type.', ['type' => $requiredType])
                    );
                }
            }

            if ($types->duplicates()->isNotEmpty()) {
                $validator->errors()->add('sequences', __('Hay tipos de documento duplicados.'));
            }
        });
    }

    /**
     * @return array{
     *     settings: array<string, array{value: string, type: string, group: string, description: string}>,
     *     sequences: list<array{document_type: string, prefix: string, padding: int, reset_counter: bool}>
     * }
     */
    public function payload(): array
    {
        $iva = number_format((float) $this->input('tax_iva_rate'), 2, '.', '');
        $mimes = array_values($this->input('attachments_allowed_mimes', []));

        /** @var list<array{document_type: string, prefix: string, padding: int, reset_counter: bool}> $sequences */
        $sequences = $this->input('sequences', []);

        return [
            'settings' => [
                TotalsCalculator::IVA_SETTING_KEY => [
                    'value' => $iva,
                    'type' => 'decimal',
                    'group' => 'tax',
                    'description' => 'Tasa de IVA (%) aplicable a nuevos documentos',
                ],
                UploadOrderAttachment::MAX_SIZE_SETTING_KEY => [
                    'value' => (string) $this->integer('attachments_max_size_kb'),
                    'type' => 'integer',
                    'group' => 'attachments',
                    'description' => 'Tamaño máximo de adjuntos en KB',
                ],
                UploadOrderAttachment::ALLOWED_MIMES_SETTING_KEY => [
                    'value' => json_encode($mimes, JSON_THROW_ON_ERROR),
                    'type' => 'json',
                    'group' => 'attachments',
                    'description' => 'Tipos MIME permitidos para adjuntos',
                ],
            ],
            'sequences' => $sequences,
        ];
    }
}
