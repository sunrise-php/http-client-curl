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

namespace Sunrise\Http\Client\Curl\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

use function curl_multi_strerror;

class ClientException extends RuntimeException implements ClientExceptionInterface
{
    /**
     * @throws self
     */
    final public static function assertCurlMultiStatusCodeSame(int $expectedStatusCode, int $actualStatusCode): void
    {
        if ($expectedStatusCode === $actualStatusCode) {
            return;
        }

        /** @var string $message */
        $message = curl_multi_strerror($actualStatusCode);

        throw new self($message, $actualStatusCode);
    }
}
