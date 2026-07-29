<?php

namespace App\Http\Requests;

use App\Models\Quotation;

class UpdateQuotationRequest extends StoreQuotationRequest
{
    public function authorize(): bool
    {
        /** @var Quotation|null $quotation */
        $quotation = $this->route('quotation');

        return $quotation instanceof Quotation
            && ($this->user()?->can('update', $quotation) ?? false);
    }
}
