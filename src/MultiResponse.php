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

namespace Sunrise\Http\Client\Curl;

use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @since 2.0.0
 */
final class MultiResponse implements ResponseInterface
{
    /**
     * @var non-empty-array<array-key, ResponseInterface>
     */
    private array $responses;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(ResponseInterface ...$responses)
    {
        if ($responses === []) {
            throw new InvalidArgumentException('At least one response is expected.');
        }

        $this->responses = $responses;
    }

    /**
     * @return non-empty-array<array-key, ResponseInterface>
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getProtocolVersion(): string
    {
        throw new LogicException('Not implemented.');
    }

    public function withProtocolVersion($version): MessageInterface
    {
        throw new LogicException('Not implemented.');
    }

    public function getHeaders(): array
    {
        throw new LogicException('Not implemented.');
    }

    public function hasHeader($name): bool
    {
        throw new LogicException('Not implemented.');
    }

    public function getHeader($name): array
    {
        throw new LogicException('Not implemented.');
    }

    public function getHeaderLine($name): string
    {
        throw new LogicException('Not implemented.');
    }

    public function withHeader($name, $value): MessageInterface
    {
        throw new LogicException('Not implemented.');
    }

    public function withAddedHeader($name, $value): MessageInterface
    {
        throw new LogicException('Not implemented.');
    }

    public function withoutHeader($name): MessageInterface
    {
        throw new LogicException('Not implemented.');
    }

    public function getBody(): StreamInterface
    {
        throw new LogicException('Not implemented.');
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        throw new LogicException('Not implemented.');
    }

    public function getStatusCode(): int
    {
        throw new LogicException('Not implemented.');
    }

    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        throw new LogicException('Not implemented.');
    }

    public function getReasonPhrase(): string
    {
        throw new LogicException('Not implemented.');
    }
}
