# Simple HTTP cURL client for PHP 8.1+ implementing PSR-18

[![Build Status](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/badges/build.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/?branch=master)
[![Total Downloads](https://poser.pugx.org/sunrise/http-client-curl/downloads?format=flat)](https://packagist.org/packages/sunrise/http-client-curl)

---

## Installation

```bash
composer require sunrise/http-client-curl
```

## Quick Start

```bash
composer require sunrise/http-message
```

```php
use Sunrise\Http\Client\Curl\Client;
use Sunrise\Http\Message\RequestFactory;
use Sunrise\Http\Message\ResponseFactory;

$client = new Client(new ResponseFactory());
$request = (new RequestFactory())->createRequest('GET', 'https://www.php.net/');
$response = $client->sendRequest($request);

echo $response->getStatusCode(), PHP_EOL;
```

### cURL options

> https://www.php.net/manual/ru/curl.constants.php

```php
use Sunrise\Http\Client\Curl\Client;
use Sunrise\Http\Message\ResponseFactory;

use const CURLOPT_AUTOREFERER;
use const CURLOPT_FOLLOWLOCATION;

$client = new Client(new ResponseFactory(), [
    CURLOPT_AUTOREFERER => true,
    CURLOPT_FOLLOWLOCATION => true,
]);
```

### Parallel execution of multiple requests

```php
use Sunrise\Http\Client\Curl\Client;
use Sunrise\Http\Client\Curl\MultiRequest;
use Sunrise\Http\Message\RequestFactory;
use Sunrise\Http\Message\ResponseFactory;

$client = new Client(new ResponseFactory());

$multiRequest = new MultiRequest(
    foo: (new RequestFactory())->createRequest('GET', 'https://www.php.net/'),
    bar: (new RequestFactory())->createRequest('GET', 'https://www.php.net/'),
)

$responses = $client->sendRequest($multiRequest)->getResponses();

foreach ($responses as $key => $response) {
    // Note that you can get the response's request by its key...
    echo sprintf('%s => %d', $multiRequest->getRequests()[$key]->getUri(), $response->getStatusCode()), PHP_EOL;
}
```

## PHP-DI definitions

```php
use DI\ContainerBuilder;
use Psr\Http\Client\ClientInterface;

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinition(__DIR__ . '/../vendor/sunrise/http-client-curl/resources/definitions/client.php');

$container = $containerBuilder->build();

// See above for usage examples.
$client = $container->get(ClientInterface::class);
```

---

## Tests

```bash
composer test
```
