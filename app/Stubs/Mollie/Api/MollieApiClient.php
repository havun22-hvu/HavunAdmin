<?php

namespace Mollie\Api;

use Mollie\Api\Resources\PaymentCollection;

/**
 * Mollie API Client Stub
 *
 * Temporary stub for local development due to SSL certificate issues.
 * This will be replaced with the real mollie/mollie-api-php package on the server.
 */
class MollieApiClient
{
    protected string $apiKey;
    public PaymentCollection $payments;

    public function __construct()
    {
        $this->payments = new PaymentCollection();
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getApiKey(): string
    {
        return $this->apiKey ?? '';
    }
}
