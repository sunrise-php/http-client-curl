<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Sunrise\Http\Client\Curl\Decorator\RetryableClient;

use function DI\decorate;

return [
    'retryable_http_client.max_attempts' => 3,
    'retryable_http_client.base_delay' => 250_000,

    ClientInterface::class => decorate(
        static function (ClientInterface $previous, ContainerInterface $container): ClientInterface {
            return new RetryableClient(
                baseClient: $previous,
                maxAttempts: $container->get('retryable_http_client.max_attempts'),
                baseDelay: $container->get('retryable_http_client.base_delay'),
            );
        }
    ),
];
