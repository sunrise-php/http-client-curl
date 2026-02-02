<?php

declare(strict_types=1);

namespace Sunrise\Http\Client\Curl\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Sunrise\Http\Client\Curl\FormData;

final class FormDataTest extends TestCase
{
    public function testContract(): void
    {
        $formData = new FormData([]);
        $this->assertInstanceOf(StreamInterface::class, $formData);
    }

    public function testConstructor(): void
    {
        $formData = new FormData(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $formData->data);
    }

    public function testToString(): void
    {
        $formData = new FormData([]);
        $this->assertSame('', (string) $formData);
    }

    public function testDetach(): void
    {
        $formData = new FormData([]);
        $this->assertNull($formData->detach());
    }

    public function testGetSize(): void
    {
        $formData = new FormData([]);
        $this->assertNull($formData->getSize());
    }

    public function testTell(): void
    {
        $formData = new FormData([]);
        $this->expectException(RuntimeException::class);
        $formData->tell();
    }

    public function testEof(): void
    {
        $formData = new FormData([]);
        $this->assertTrue($formData->eof());
    }

    public function testIsSeekable(): void
    {
        $formData = new FormData([]);
        $this->assertFalse($formData->isSeekable());
    }

    public function testSeek(): void
    {
        $formData = new FormData([]);
        $this->expectException(RuntimeException::class);
        $formData->seek(0);
    }

    public function testRewind(): void
    {
        $formData = new FormData([]);
        $this->expectException(RuntimeException::class);
        $formData->rewind();
    }

    public function testIsWritable(): void
    {
        $formData = new FormData([]);
        $this->assertFalse($formData->isWritable());
    }

    public function testWrite(): void
    {
        $formData = new FormData([]);
        $this->expectException(RuntimeException::class);
        $formData->write('foo');
    }

    public function testIsReadable(): void
    {
        $formData = new FormData([]);
        $this->assertFalse($formData->isReadable());
    }

    public function testRead(): void
    {
        $formData = new FormData([]);
        $this->expectException(RuntimeException::class);
        $formData->read(1);
    }

    public function testGetContents(): void
    {
        $formData = new FormData([]);
        $this->expectException(RuntimeException::class);
        $formData->getContents();
    }

    public function testGetMetadata(): void
    {
        $formData = new FormData([]);
        $this->assertSame([], $formData->getMetadata());
        $this->assertSame(null, $formData->getMetadata('foo'));
    }
}
