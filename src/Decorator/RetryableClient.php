<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-client-curl/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-client-curl
 */

declare(strict_types=1);

namespace Sunrise\Http\Client\Curl\Decorator;

use InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function random_int;
use function usleep;

/**
 * @since 2.1.0
 */
final class RetryableClient implements ClientInterface
{
    public function __construct(
        private readonly ClientInterface $baseClient,
        private readonly int $maxAttempts,
        private readonly int $baseDelay,
    ) {
        if ($maxAttempts < 1) {
            throw new InvalidArgumentException('maxAttempts must be >= 1');
        }
        if ($baseDelay < 0) {
            throw new InvalidArgumentException('baseDelay must be >= 0');
        }
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $attempt = 0;
        while (true) {
            $attempt++;

            try {
                return $this->baseClient->sendRequest($request);
            } catch (NetworkExceptionInterface $e) {
                $attempt < $this->maxAttempts ? $this->applyDelay($attempt) : throw $e;
            }
        }
    }

    private function applyDelay(int $attempt): void
    {
        usleep($this->calculateDelay($attempt));
    }

    /**
     * @link https://aws.amazon.com/ru/blogs/architecture/exponential-backoff-and-jitter/
     */
    private function calculateDelay(int $attempt): int
    {
        // full jitter
        return random_int(0, $this->baseDelay * (2 ** ($attempt - 1)));
    }
}
