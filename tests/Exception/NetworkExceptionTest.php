<?php

declare(strict_types=1);

namespace Sunrise\Http\Client\Curl\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Sunrise\Http\Client\Curl\Exception\NetworkException;
use Throwable;

final class NetworkExceptionTest extends TestCase
{
    public function testConstructor(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $previous = $this->createMock(Throwable::class);
        $exception = new NetworkException($request, 'foo', 255, $previous);
        self::assertSame($request, $exception->getRequest());
        self::assertSame('foo', $exception->getMessage());
        self::assertSame(255, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
