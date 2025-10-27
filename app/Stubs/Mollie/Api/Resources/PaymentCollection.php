<?php

namespace Mollie\Api\Resources;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * Payment Collection Stub
 *
 * Returns test payment data for local development
 */
class PaymentCollection implements IteratorAggregate
{
    protected array $payments = [];

    public function page(?int $from = null, int $limit = 50): self
    {
        // Return empty collection for now (no test data needed)
        // Real data will come from actual Mollie API on production
        return $this;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->payments);
    }

    public function count(): int
    {
        return count($this->payments);
    }
}
