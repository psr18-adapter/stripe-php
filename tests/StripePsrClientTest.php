<?php

declare(strict_types=1);

namespace Psr18Adapter\Stripe\Tests;

use Http\Mock\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr18Adapter\Stripe\StripePsr18Client;
use Stripe\ApiRequestor;

class StripePsrClientTest extends TestCase
{
    /** @var ApiRequestor */
    private $requestor;
    /** @var Client */
    private $decoratedClient;

    public function setUp(): void
    {
        ApiRequestor::setHttpClient(
            new StripePsr18Client($this->decoratedClient = new Client(), new Psr17Factory(), new Psr17Factory())
        );
        $this->requestor = new ApiRequestor('key');
        $this->decoratedClient->addResponse(new Response(200, [], '{}'));
    }

    public function testRequest(): void
    {
        $this->requestor->request(
            'post',
            '/oauth/token',
            ['code' => 'foo', 'grant_type' => 'bar'],
            ['Idempotency-Key' => 'foo']
        );

        $request = $this->decoratedClient->getLastRequest();

        $uri = $request->getUri();
        self::assertSame('post', $request->getMethod());
        self::assertSame('api.stripe.com', $uri->getHost());
        self::assertSame('/oauth/token', $uri->getPath());
        self::assertSame('code=foo&grant_type=bar', $uri->getQuery());
        self::assertSame('foo', $request->getHeaderLine('Idempotency-Key'));
    }
}
