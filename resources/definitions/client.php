<?php

declare(strict_types=1);

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Sunrise\Http\Client\Curl\Client;

use function DI\create;
use function DI\get;

return [
    'curl.options' => [],
    'curl.multi_select_timeout' => null,
    'curl.multi_select_sleep_duration' => null,

    ClientInterface::class => create(Client::class)
        ->constructor(
            responseFactory: get(ResponseFactoryInterface::class),
            curlOptions: get('curl.options'),
            curlMultiSelectTimeout: get('curl.multi_select_timeout'),
            curlMultiSelectSleepDuration: get('curl.multi_select_sleep_duration'),
        ),
];
