## HTTP cURL client for PHP 7.1+ (incl. PHP 8) based on PSR-18

[![Gitter](https://badges.gitter.im/sunrise-php/support.png)](https://gitter.im/sunrise-php/support)
[![Build Status](https://circleci.com/gh/sunrise-php/http-client-curl.svg?style=shield)](https://circleci.com/gh/sunrise-php/http-client-curl)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/?branch=master)
[![Total Downloads](https://poser.pugx.org/sunrise/http-client-curl/downloads)](https://packagist.org/packages/sunrise/http-client-curl)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-client-curl/v/stable)](https://packagist.org/packages/sunrise/http-client-curl)
[![License](https://poser.pugx.org/sunrise/http-client-curl/license)](https://packagist.org/packages/sunrise/http-client-curl)

---

## Installation

```bash
composer require sunrise/http-client-curl
```

## How to use?

### Sunrise HTTP Factory

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

// just use PSR-7 Response object...
```

### Zend Diactoros

```bash
composer require zendframework/zend-diactoros
```

```php
use Sunrise\Http\Client\Curl\Client;
use Zend\Diactoros\RequestFactory;
use Zend\Diactoros\ResponseFactory;

$client = new Client(new ResponseFactory());
$request = (new RequestFactory)->createRequest('GET', 'http://php.net/');
$response = $client->sendRequest($request);

// just use PSR-7 Response object...
```

### cURL options

```php
$client = new Client(new ResponseFactory(), [
    \CURLOPT_AUTOREFERER => true,
    \CURLOPT_FOLLOWLOCATION => true,
    \CURLOPT_MAXREDIRS => 10,
]);
```

### Asynchronous execution of multiple requests

```php
$requests = [
    (new RequestFactory)->createRequest('GET', 'http://php.net/'),
    (new RequestFactory)->createRequest('GET', 'http://php.net/'),
    (new RequestFactory)->createRequest('GET', 'http://php.net/'),
];

$client = new Client(new ResponseFactory());
$responses = $client->sendRequests(...$request);

foreach ($responses as $response) {
    // just use PSR-7 Response object...
}
```

---

## Test run

```bash
composer test
```

---

## Useful links

* http://php.net/manual/en/intro.curl.php
* https://curl.haxx.se/libcurl/c/libcurl-errors.html
* https://www.php-fig.org/psr/psr-2/
* https://www.php-fig.org/psr/psr-7/
* https://www.php-fig.org/psr/psr-17/
* https://www.php-fig.org/psr/psr-18/
