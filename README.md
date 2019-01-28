## HTTP cURL client for PHP 7.1+ based on PSR-18

[![Gitter](https://badges.gitter.im/sunrise-php/support.png)](https://gitter.im/sunrise-php/support)
[![Build Status](https://api.travis-ci.com/sunrise-php/http-client-curl.svg?branch=master)](https://travis-ci.com/sunrise-php/http-client-curl)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-client-curl/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-client-curl/v/stable)](https://packagist.org/packages/sunrise/http-client-curl)
[![Total Downloads](https://poser.pugx.org/sunrise/http-client-curl/downloads)](https://packagist.org/packages/sunrise/http-client-curl)
[![License](https://poser.pugx.org/sunrise/http-client-curl/license)](https://packagist.org/packages/sunrise/http-client-curl)

## Installation

```
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
use Sunrise\Http\Factory\StreamFactory;

$client = new Client(new ResponseFactory(), new StreamFactory());
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
use Zend\Diactoros\StreamFactory;

$client = new Client(new ResponseFactory(), new StreamFactory());
$request = (new RequestFactory)->createRequest('GET', 'http://php.net/');
$response = $client->sendRequest($request);

// just use PSR-7 Response object...
```

### cURL options

```php
$client = new Client(new ResponseFactory(), new StreamFactory(), [
    \CURLOPT_AUTOREFERER => true,
    \CURLOPT_FOLLOWLOCATION => true,
    \CURLOPT_MAXREDIRS => 10,
]);
```

## Test run

```bash
php vendor/bin/phpunit --colors=always --coverage-text
```

## Api documentation

https://phpdoc.fenric.ru/

## Useful links

* http://php.net/manual/en/intro.curl.php
* https://curl.haxx.se/libcurl/c/libcurl-errors.html
* https://www.php-fig.org/psr/psr-2/
* https://www.php-fig.org/psr/psr-7/
* https://www.php-fig.org/psr/psr-17/
* https://www.php-fig.org/psr/psr-18/

## Team

<table>
    <tbody>
        <tr>
            <td>
                <img src="https://avatars2.githubusercontent.com/u/9021747?s=72&v=4">
                <br>
                <a href="https://github.com/peter279k">@peter279k</a>
            </td>
            <td>
                <img src="https://avatars1.githubusercontent.com/u/2872934?s=72&v=4">
                <br>
                <a href="https://github.com/fenric">@fenric</a>
            </td>
        </tr>
    </tbody>
</table>
