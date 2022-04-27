# Simple HTTP cURL client for PHP 7.1+ based on PSR-18

[![Build Status](https://circleci.com/gh/sunrise-php/http-client-curl.svg?style=shield)](https://circleci.com/gh/sunrise-php/http-client-curl)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/?branch=master)
[![Total Downloads](https://poser.pugx.org/sunrise/http-client-curl/downloads?format=flat)](https://packagist.org/packages/sunrise/http-client-curl)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-client-curl/v/stable?format=flat)](https://packagist.org/packages/sunrise/http-client-curl)
[![License](https://poser.pugx.org/sunrise/http-client-curl/license?format=flat)](https://packagist.org/packages/sunrise/http-client-curl)

---

## Installation

```bash
composer require sunrise/http-client-curl
```

## QuickStart

```bash
composer require sunrise/http-factory
```

```php
use Sunrise\Http\Client\Curl\Client;
use Sunrise\Http\Factory\RequestFactory;
use Sunrise\Http\Factory\ResponseFactory;

$client = new Client(new ResponseFactory());
$request = (new RequestFactory)->createRequest('GET', 'http://php.net/');
$response = $client->sendRequest($request);

echo $response->getStatusCode(), PHP_EOL;
```

### cURL options

> https://www.php.net/manual/ru/curl.constants.php

```php
$client = new Client(new ResponseFactory(), [
    \CURLOPT_AUTOREFERER => true,
    \CURLOPT_FOLLOWLOCATION => true,
    \CURLOPT_MAXREDIRS => 10,
]);
```

### Parallel execution of multiple requests

```php
$requests = [
    (new RequestFactory)->createRequest('GET', 'http://php.net/'),
    (new RequestFactory)->createRequest('GET', 'http://php.net/'),
];

$client = new Client(new ResponseFactory());
$responses = $client->sendRequests(...$request);

foreach ($responses as $i => $response) {
    // note that you can get the response's request...
    echo sprintf('%d <= %s', $response->getStatusCode(), $requests[$i]->getUri()), PHP_EOL;
}
```

---

## Test run

```bash
composer test
```

## Useful links

* http://php.net/manual/en/intro.curl.php
* https://curl.haxx.se/libcurl/c/libcurl-errors.html
* https://www.php-fig.org/psr/psr-2/
* https://www.php-fig.org/psr/psr-7/
* https://www.php-fig.org/psr/psr-17/
* https://www.php-fig.org/psr/psr-18/
