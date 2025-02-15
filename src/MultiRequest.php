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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

use function current;

/**
 * @since 2.0.0
 */
final class MultiRequest implements RequestInterface
{
    /**
     * @var non-empty-array<array-key, RequestInterface>
     */
    private array $requests;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(RequestInterface ...$requests)
    {
        if ($requests === []) {
            throw new InvalidArgumentException('At least one request is expected.');
        }

        $this->requests = $requests;
    }

    /**
     * @return non-empty-array<array-key, RequestInterface>
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    public function getProtocolVersion(): string
    {
        return current($this->requests)->getProtocolVersion();
    }

    public function withProtocolVersion($version): static
    {
        throw new LogicException('Not implemented.');
    }

    public function getHeaders(): array
    {
        return current($this->requests)->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return current($this->requests)->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return current($this->requests)->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return current($this->requests)->getHeaderLine($name);
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
        return current($this->requests)->getBody();
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        throw new LogicException('Not implemented.');
    }

    public function getRequestTarget(): string
    {
        return current($this->requests)->getRequestTarget();
    }

    public function withRequestTarget($requestTarget): RequestInterface
    {
        throw new LogicException('Not implemented.');
    }

    public function getMethod(): string
    {
        return current($this->requests)->getMethod();
    }

    public function withMethod($method): RequestInterface
    {
        throw new LogicException('Not implemented.');
    }

    public function getUri(): UriInterface
    {
        return current($this->requests)->getUri();
    }

    public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
    {
        throw new LogicException('Not implemented.');
    }
}
