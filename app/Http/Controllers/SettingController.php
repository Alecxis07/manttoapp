<?php

namespace App\Http\Controllers;

use App\Actions\Maintenance\UploadOrderAttachment;
use App\Actions\Settings\UpdateDocumentSequences;
use App\Actions\Settings\UpdateSettings;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\DocumentSequence;
use App\Models\Setting;
use App\Services\TotalsCalculator;
use App\Support\FolioGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function edit(Request $request): Response
    {
        abort_unless($request->user()?->can('settings.*') ?? false, 403);

        $settings = Setting::query()
            ->whereIn('key', [
                TotalsCalculator::IVA_SETTING_KEY,
                UploadOrderAttachment::MAX_SIZE_SETTING_KEY,
                UploadOrderAttachment::ALLOWED_MIMES_SETTING_KEY,
            ])
            ->get()
            ->keyBy('key');

        $iva = $settings->get(TotalsCalculator::IVA_SETTING_KEY)?->value
            ?? TotalsCalculator::DEFAULT_IVA_RATE;

        $maxSizeKb = $settings->get(UploadOrderAttachment::MAX_SIZE_SETTING_KEY)?->value
            ?? (string) UploadOrderAttachment::DEFAULT_MAX_SIZE_KB;

        $mimesRaw = $settings->get(UploadOrderAttachment::ALLOWED_MIMES_SETTING_KEY)?->value;
        $mimes = UploadOrderAttachment::DEFAULT_ALLOWED_MIMES;

        if (is_string($mimesRaw) && $mimesRaw !== '') {
            $decoded = json_decode($mimesRaw, true);
            if (is_array($decoded) && $decoded !== []) {
                $mimes = array_values(array_map('strval', $decoded));
            }
        }

        $year = (int) now(config('app.timezone', 'America/Mexico_City'))->format('Y');
        $existingSequences = DocumentSequence::query()
            ->get()
            ->keyBy('document_type');

        $sequences = collect(FolioGenerator::DEFAULT_PREFIXES)
            ->map(function (string $defaultPrefix, string $documentType) use ($existingSequences, $year): array {
                $sequence = $existingSequences->get($documentType);

                return [
                    'document_type' => $documentType,
                    'label' => $this->sequenceLabel($documentType),
                    'prefix' => $sequence?->prefix ?? $defaultPrefix,
                    'padding' => $sequence?->padding ?? 5,
                    'year' => $sequence?->year ?? $year,
                    'last_number' => $sequence?->last_number ?? 0,
                    'reset_counter' => false,
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Settings/Edit', [
            'settings' => [
                'tax_iva_rate' => $iva,
                'attachments_max_size_kb' => (int) $maxSizeKb,
                'attachments_allowed_mimes' => implode("\n", $mimes),
            ],
            'sequences' => $sequences,
            'defaultMimes' => UploadOrderAttachment::DEFAULT_ALLOWED_MIMES,
        ]);
    }

    public function update(
        UpdateSettingsRequest $request,
        UpdateSettings $updateSettings,
        UpdateDocumentSequences $updateDocumentSequences,
    ): RedirectResponse {
        $payload = $request->payload();

        $updateSettings->handle($payload['settings']);
        $updateDocumentSequences->handle($payload['sequences']);

        return redirect()
            ->route('settings.edit')
            ->with('success', __('Configuración actualizada correctamente.'));
    }

    private function sequenceLabel(string $documentType): string
    {
        return match ($documentType) {
            'maintenance_order' => 'Órdenes de mantenimiento',
            'quotation' => 'Cotizaciones',
            'billing_request' => 'Solicitudes de facturación',
            default => $documentType,
        };
    }
}
