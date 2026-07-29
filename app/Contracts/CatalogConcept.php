<?php

namespace App\Contracts;

/**
 * Catalog items that may be referenced by operational documents.
 *
 * RN-CAT-001: concepts in use cannot be physically deleted — only deactivated.
 */
interface CatalogConcept
{
    /**
     * Whether this concept is referenced by orders, quotations, or child catalog items.
     *
     * Service/part usage against maintenance_order_items / quotation_items will be
     * wired when those modules exist (Fases 5/7). Until then, implementations return
     * a safe stub (false) except where an existing relation can be checked
     * (e.g. categories with services).
     */
    public function isInUse(): bool;
}
