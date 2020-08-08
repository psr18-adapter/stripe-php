<?php

declare(strict_types=1);

namespace Psr18Adapter\Stripe;

use Stripe\ApiRequestor;
use Stripe\StripeClientInterface;

final class StripeClientAccessor
{
    private function __construct() {}

    public static function access(StripeClientInterface $stripe, StripePsr18Client $adapter): StripeClientInterface
    {
        ApiRequestor::setHttpClient($adapter);

        return $stripe;
    }
}