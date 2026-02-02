<?php

declare(strict_types=1);

namespace Sunrise\Http\Client\Curl\Tests;

use CURLFile;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Client\Curl\File;

final class FileTest extends TestCase
{
    public function testContract(): void
    {
        $file = new File('foo', 'bar/baz');
        $this->assertInstanceOf(CURLFile::class, $file);
    }

    public function testConstructor(): void
    {
        $file = new File('foo', 'bar/baz');
        $this->assertSame('foo', $file->getFilename());
        $this->assertSame('bar/baz', $file->getMimeType());
    }
}
