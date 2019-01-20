<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-client-curl/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-client-curl
 */

namespace Sunrise\Http\Client\Curl\Exception;

/**
 * Import classes
 */
use RuntimeException;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * ClientException
 */
class ClientException extends RuntimeException implements ClientExceptionInterface
{
}
