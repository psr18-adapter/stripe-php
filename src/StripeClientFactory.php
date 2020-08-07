<?php

declare(strict_types=1);

namespace Psr18Adapter\Stripe;

use Stripe\ApiRequestor;
use Stripe\StripeClientInterface;

final class StripeClientFactory
{
    private function __construct() {}

    public static function create(StripeClientInterface $stripe, StripePsr18Client $adapter): StripeClientInterface
    {
        ApiRequestor::setHttpClient($adapter);

        return $stripe;
    }
}