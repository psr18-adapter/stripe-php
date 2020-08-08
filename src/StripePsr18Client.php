<?php

declare(strict_types=1);

namespace Psr18Adapter\Stripe;

use Psr\Http\Client\ClientInterface as PsrClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Stripe\HttpClient\ClientInterface;

class StripePsr18Client implements ClientInterface
{
    /** @var PsrClientInterface */
    private $client;
    /** @var UriFactoryInterface */
    private $uriFactory;
    /** @var RequestFactoryInterface */
    private $requestFactory;

    public function __construct(
        PsrClientInterface $stripeClient,
        UriFactoryInterface  $uriFactory,
        RequestFactoryInterface $requestFactory
    ) {
        $this->client = $stripeClient;
        $this->uriFactory = $uriFactory;
        $this->requestFactory = $requestFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $method The HTTP method being used
     * @param string $absUrl The URL being requested, including domain and protocol
     * @param string[] $headers
     * @param string[] $params KV pairs for parameters. Can be nested for arrays and hashes
     * @param bool $hasFile Whether or not $params references a file (via an @ prefix or CurlFile)
     *
     * @return array{0: string, 1: int, 2: array<string, list<string>>}
     */
    public function request($method, $absUrl, $headers, $params, $hasFile): array
    {
        $request = $this->requestFactory->createRequest(
            $method,
            $this->uriFactory->createUri($absUrl)->withQuery(http_build_query($params, '', '&', PHP_QUERY_RFC3986))
        );

        foreach ($headers as $header) {
            $request = $request->withHeader($key = strstr($header, ':', true), substr($header, strlen($key) + 2));
        }

        $response = $this->client->sendRequest($request);

        return [$response->getBody()->__toString(), $response->getStatusCode(), $response->getHeaders()];
    }
}