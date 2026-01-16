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

use CURLFile;
use Psr\Http\Message\StreamInterface;
use Sunrise\Http\Message\Exception\RuntimeException;

use function array_walk_recursive;

/**
 * @since 2.2.0
 */
final class FormData implements StreamInterface
{
    /**
     * @var array<array-key, mixed>
     */
    public readonly array $data;

    /**
     * @param array<array-key, mixed> $data
     */
    public function __construct(array $data)
    {
        array_walk_recursive($data, static function (mixed &$value): void {
            if ($value instanceof StreamInterface) {
                /** @var string $uri */
                $uri = $value->getMetadata('uri');
                $value = new CURLFile($uri);
            }
        });

        $this->data = $data;
    }

    public function __toString(): string
    {
        return '';
    }

    public function close(): void
    {
    }

    public function detach(): mixed
    {
        return null;
    }

    public function getSize(): ?int
    {
        return null;
    }

    public function tell(): int
    {
        throw new RuntimeException('Stream does not support the tell operation');
    }

    public function eof(): bool
    {
        return true;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        throw new RuntimeException('Stream is not seekable');
    }

    public function rewind(): void
    {
        throw new RuntimeException('Stream is not seekable');
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write(string $string): int
    {
        throw new RuntimeException('Stream is not writable');
    }

    public function isReadable(): bool
    {
        return false;
    }

    public function read(int $length): string
    {
        throw new RuntimeException('Stream is not readable');
    }

    public function getContents(): string
    {
        throw new RuntimeException('Stream is not readable');
    }

    public function getMetadata(?string $key = null): mixed
    {
        return $key === null ? [] : null;
    }
}
