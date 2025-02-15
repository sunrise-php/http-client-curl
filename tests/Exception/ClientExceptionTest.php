<?php

declare(strict_types=1);

namespace Sunrise\Http\Client\Curl\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Client\Curl\Exception\ClientException;

use const CURLM_INTERNAL_ERROR;
use const CURLM_OK;

final class ClientExceptionTest extends TestCase
{
    public function testAssertCurlMultiStatusCodeSame(): void
    {
        ClientException::assertCurlMultiStatusCodeSame(CURLM_OK, CURLM_OK);
        $this->expectException(ClientException::class);
        ClientException::assertCurlMultiStatusCodeSame(CURLM_OK, CURLM_INTERNAL_ERROR);
    }
}
