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
use Throwable;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

/**
 * NetworkException
 */
class NetworkException extends ClientException implements NetworkExceptionInterface
{

    /**
     * Request instance
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Constructor of the class
     *
     * @param RequestInterface $request
     * @param string $message
     * @param int $code
     * @param Throwable $previous
     */
    public function __construct(
        RequestInterface $request,
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        $this->request = $request;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritDoc}
     */
    public function getRequest() : RequestInterface
    {
        return $this->request;
    }
}
