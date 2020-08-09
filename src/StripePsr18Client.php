<?php

declare(strict_types=1);

namespace Psr18Adapter\Stripe;

use Psr\Http\Client\ClientInterface as PsrClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
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
        $response = $this->client->sendRequest(
            $this->setNormalizedHeaders(
                $this->requestFactory->createRequest(
                    $method,
                    $this->uriFactory->createUri($absUrl)->withQuery(http_build_query($params))
                ),
                $headers
            )
        );

        return [$response->getBody()->__toString(), $response->getStatusCode(), $response->getHeaders()];
    }

    /**
     * @param list<string> $rawHeaders
     */
    private function setNormalizedHeaders(RequestInterface $request, array $rawHeaders): RequestInterface
    {
        foreach ($rawHeaders as $header) {
            $key = strstr($header, ':', true);
            $value = substr($header, strlen($key) + 2);
            // These headers must not be sent with empty values, otherwise you get error responses such as "Only Stripe Connect platforms can work with other accounts."
            if (in_array($key, ['Stripe-Account', 'Stripe-Version']) && !$value) {
                continue;
            }
            $request = $request->withHeader($key, $value);
        }

        return $request;
    }
}