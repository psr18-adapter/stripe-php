<?php

declare(strict_types=1);

namespace Psr18Adapter\Stripe\Tests;

use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr18Adapter\Stripe\StripePsr18Client;
use Stripe\ApiRequestor;
use Stripe\Collection;
use Stripe\StripeClient;

class IntegrationTest extends TestCase
{
    /** @var StripeClient */
    private $stripeClient;
    public function setUp(): void
    {
        if (empty($_SERVER['STRIPE_CLIENT_ID'])) {
            self::markTestSkipped('STRIPE_CLIENT_ID environment variable needs to be set to run integration tests');
        }

        ApiRequestor::setHttpClient(new StripePsr18Client(new Client(), new Psr17Factory(), new Psr17Factory()));
        $this->stripeClient = new StripeClient($_SERVER['STRIPE_CLIENT_ID']);
    }

    public function testGetAllCustomers(): void
    {
        self::assertInstanceOf(Collection::class, $this->stripeClient->customers->all(['limit' => 1]));
    }
}