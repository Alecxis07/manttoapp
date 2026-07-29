<?php

namespace App\Actions\Settings;

use App\Models\DocumentSequence;
use App\Support\FolioGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateDocumentSequences
{
    /**
     * @param  list<array{document_type: string, prefix: string, padding: int, reset_counter?: bool}>  $sequences
     */
    public function handle(array $sequences): void
    {
        DB::transaction(function () use ($sequences): void {
            $year = (int) now(config('app.timezone', 'America/Mexico_City'))->format('Y');

            foreach ($sequences as $row) {
                $documentType = $row['document_type'];

                if (! array_key_exists($documentType, FolioGenerator::DEFAULT_PREFIXES)) {
                    throw ValidationException::withMessages([
                        'sequences' => __('Tipo de documento desconocido: :type', ['type' => $documentType]),
                    ]);
                }

                $sequence = DocumentSequence::query()->firstOrNew([
                    'document_type' => $documentType,
                ]);

                if (! $sequence->exists) {
                    $sequence->year = $year;
                    $sequence->last_number = 0;
                }

                $sequence->prefix = strtoupper(trim($row['prefix']));
                $sequence->padding = max(1, (int) $row['padding']);

                if (($row['reset_counter'] ?? false) === true) {
                    $sequence->year = $year;
                    $sequence->last_number = 0;
                }

                $sequence->save();
            }
        });
    }
}
