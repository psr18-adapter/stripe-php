#  psr18-adapter/stripe-php

## Install

Via [Composer](https://getcomposer.org/doc/00-intro.md)

```bash
composer require psr18-adapter/stripe-php
```
## Usage

`stripe-php` library uses singleton for setting the http client, so you will have to call it at some point 
before you start calling Stripe itself, like so

```php
\Stripe\ApiRequestor::setHttpClient(
    new \Psr18Adapter\Stripe\StripePsr18Client($psr18Client, $psr7UriFactory, $psr7RequestFactory)
);
```

### How to set up for dependency injection containers
Singletons like ApiRequestor cannot really be configured for dependency injection containers like 
`symfony/dependency-injection` without writing extra layer as well. This is why I'm shipping such layer within 
this package too, in case you are also finding that you need to write such layer, but don't really want to.

It's used like following, if you use YAML and `symfony/dependency-injection`

```yaml
services:
    Stripe\StripeClient:
      factory: ['Psr18Adapter\Stripe\StripeClientAccessor', 'access']
      arguments:
        - !service
          class: Stripe\StripeClient
          arguments:
              $config:
                api_key: '%stripe_secret_key%'
        - !service
          class: Psr18Adapter\Stripe\StripePsr18Client
          autowire: true
```

This should be a replacement of definition of `Stripe\StripeClient` service itself. 

Advantage of this approach is that 
it ensures http client is set in singleton before retrieving `\Stripe\StripeClient` service. This means you can be 
confident any time StripeClient is fetched from container, it's already configured with your PSR-18 HTTP client.

## Licensing

MIT license. Please see [License File](LICENSE) for more information.
