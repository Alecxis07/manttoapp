<?php

namespace App\Support;

use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class FolioGenerator
{
    /**
     * @var array<string, string>
     */
    public const DEFAULT_PREFIXES = [
        'maintenance_order' => 'ORD',
        'quotation' => 'COT',
        'billing_request' => 'FAC',
    ];

    public function generate(string $documentType, ?int $year = null): string
    {
        if (! array_key_exists($documentType, self::DEFAULT_PREFIXES)) {
            throw new InvalidArgumentException("Unknown document type [{$documentType}].");
        }

        $year ??= (int) now(config('app.timezone', 'America/Mexico_City'))->format('Y');

        return DB::transaction(function () use ($documentType, $year): string {
            $sequence = DocumentSequence::query()
                ->where('document_type', $documentType)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                $sequence = DocumentSequence::query()->create([
                    'document_type' => $documentType,
                    'prefix' => self::DEFAULT_PREFIXES[$documentType],
                    'year' => $year,
                    'last_number' => 0,
                    'padding' => 5,
                ]);

                $sequence = DocumentSequence::query()
                    ->whereKey($sequence->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            if ((int) $sequence->year !== $year) {
                $sequence->year = $year;
                $sequence->last_number = 0;
            }

            $sequence->last_number = (int) $sequence->last_number + 1;
            $sequence->save();

            $padding = max(1, (int) $sequence->padding);
            $number = str_pad((string) $sequence->last_number, $padding, '0', STR_PAD_LEFT);

            if ($sequence->prefix === null || $sequence->prefix === '') {
                throw new RuntimeException("Document sequence [{$documentType}] is missing a prefix.");
            }

            return sprintf('%s-%d-%s', $sequence->prefix, $year, $number);
        });
    }
}
