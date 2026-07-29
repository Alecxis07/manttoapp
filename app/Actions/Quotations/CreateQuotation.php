<?php

namespace App\Actions\Quotations;

use App\DTOs\QuotationData;
use App\Enums\QuotationItemType;
use App\Enums\QuotationStatus;
use App\Models\PartCatalog;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationStatusHistory;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Services\TotalsCalculator;
use App\Support\DiscountLimiter;
use App\Support\FolioGenerator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CreateQuotation
{
    public function __construct(
        private FolioGenerator $folioGenerator,
        private TotalsCalculator $totalsCalculator,
        private DiscountLimiter $discountLimiter,
    ) {}

    public function handle(QuotationData $data, User $actor): Quotation
    {
        return DB::transaction(function () use ($data, $actor): Quotation {
            $preparedItems = $this->prepareItems($data->items);
            $this->assertDiscountAllowed($preparedItems, $actor);

            $taxRate = $this->totalsCalculator->resolveTaxRate();
            $totals = $this->totalsCalculator->calculate($preparedItems, $taxRate);

            $quotation = Quotation::query()->create([
                'folio' => $this->folioGenerator->generate('quotation'),
                'version' => 1,
                'customer_id' => $data->customerId,
                'vehicle_id' => $data->vehicleId,
                'status' => QuotationStatus::Draft,
                'valid_until' => $data->validUntil,
                'commercial_terms' => $data->commercialTerms,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'total' => $totals['total'],
                'tax_rate' => $totals['tax_rate'],
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->syncItems($quotation, $preparedItems);

            QuotationStatusHistory::query()->create([
                'quotation_id' => $quotation->id,
                'from_status' => null,
                'to_status' => QuotationStatus::Draft,
                'user_id' => $actor->id,
                'notes' => 'Cotización creada',
                'created_at' => now(),
            ]);

            return $quotation->fresh(['items', 'customer', 'vehicle']);
        });
    }

    /**
     * @param  list<array{
     *     item_type: string,
     *     service_catalog_id?: int|null,
     *     part_catalog_id?: int|null,
     *     code?: string|null,
     *     description: string,
     *     quantity: float|int|string,
     *     unit_price: float|int|string,
     *     discount?: float|int|string,
     *     notes?: string|null
     * }>  $items
     * @return list<array{
     *     item_type: QuotationItemType,
     *     service_catalog_id: int|null,
     *     part_catalog_id: int|null,
     *     code: string|null,
     *     description: string,
     *     quantity: string,
     *     unit_price: string,
     *     discount: string,
     *     line_total: string,
     *     notes: string|null,
     *     sort_order: int
     * }>
     */
    public function prepareItems(array $items): array
    {
        $prepared = [];

        foreach (array_values($items) as $index => $item) {
            $type = QuotationItemType::from($item['item_type']);
            $quantity = number_format((float) $item['quantity'], 2, '.', '');
            $unitPrice = number_format((float) $item['unit_price'], 2, '.', '');
            $discount = number_format((float) ($item['discount'] ?? 0), 2, '.', '');
            $lineGross = bcmul($quantity, $unitPrice, 2);
            $lineTotal = bcsub($lineGross, $discount, 2);

            if (bccomp($lineTotal, '0', 2) === -1) {
                $lineTotal = '0.00';
            }

            $code = $item['code'] ?? null;
            $description = $item['description'];
            $serviceCatalogId = isset($item['service_catalog_id']) ? (int) $item['service_catalog_id'] : null;
            $partCatalogId = isset($item['part_catalog_id']) ? (int) $item['part_catalog_id'] : null;

            if ($type === QuotationItemType::Service && $serviceCatalogId) {
                $catalog = ServiceCatalog::query()->find($serviceCatalogId);
                if ($catalog !== null) {
                    $code ??= $catalog->code;
                    if ($description === '' || $description === null) {
                        $description = $catalog->description;
                    }
                }
            }

            if ($type === QuotationItemType::Part && $partCatalogId) {
                $catalog = PartCatalog::query()->find($partCatalogId);
                if ($catalog !== null) {
                    $code ??= $catalog->code;
                    if ($description === '' || $description === null) {
                        $description = $catalog->description;
                    }
                }
            }

            $prepared[] = [
                'item_type' => $type,
                'service_catalog_id' => $type === QuotationItemType::Service ? $serviceCatalogId : null,
                'part_catalog_id' => $type === QuotationItemType::Part ? $partCatalogId : null,
                'code' => $code,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'line_total' => $lineTotal,
                'notes' => $item['notes'] ?? null,
                'sort_order' => $index,
            ];
        }

        return $prepared;
    }

    /**
     * @param  list<array{quantity: string, unit_price: string, discount: string}>  $preparedItems
     */
    public function assertDiscountAllowed(array $preparedItems, User $actor): void
    {
        $totals = $this->totalsCalculator->calculate($preparedItems);
        $percent = $this->discountLimiter->percentOf($totals['subtotal'], $totals['discount_total']);

        if (! $this->discountLimiter->allows($actor, $percent)) {
            throw new AuthorizationException(
                __('El descuento del :percent% excede el límite autorizado para su rol.', [
                    'percent' => number_format($percent, 2),
                ])
            );
        }
    }

    /**
     * @param  list<array{
     *     item_type: QuotationItemType,
     *     service_catalog_id: int|null,
     *     part_catalog_id: int|null,
     *     code: string|null,
     *     description: string,
     *     quantity: string,
     *     unit_price: string,
     *     discount: string,
     *     line_total: string,
     *     notes: string|null,
     *     sort_order: int
     * }>  $preparedItems
     */
    public function syncItems(Quotation $quotation, array $preparedItems): void
    {
        $quotation->items()->delete();

        foreach ($preparedItems as $item) {
            QuotationItem::query()->create([
                'quotation_id' => $quotation->id,
                ...$item,
            ]);
        }
    }
}
