<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-client-curl/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-client-curl
 */

namespace Sunrise\Http\Client\Curl;

/**
 * Import classes
 */
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Sunrise\Http\Client\Curl\Exception\ClientException;
use Sunrise\Http\Client\Curl\Exception\NetworkException;

/**
 * Import functions
 */
use function curl_close;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt_array;
use function explode;
use function sprintf;
use function strpos;
use function substr;
use function trim;

/**
 * Import constants
 */
use const CURLINFO_HEADER_SIZE;
use const CURLINFO_RESPONSE_CODE;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_HEADER;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_URL;

/**
 * HTTP Client based on CURL
 *
 * @link http://php.net/manual/en/intro.curl.php
 * @link https://curl.haxx.se/libcurl/c/libcurl-errors.html
 * @link https://www.php-fig.org/psr/psr-2/
 * @link https://www.php-fig.org/psr/psr-7/
 * @link https://www.php-fig.org/psr/psr-17/
 * @link https://www.php-fig.org/psr/psr-18/
 */
class Client implements ClientInterface
{

    /**
     * Response Factory
     *
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Stream Factory
     *
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * CURL options
     *
     * @var array
     */
    protected $curlOptions;

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface $streamFactory
     * @param array $curlOptions
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        array $curlOptions = []
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->curlOptions = $curlOptions;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ClientException
     * @throws NetworkException
     */
    public function sendRequest(RequestInterface $request) : ResponseInterface
    {
        $handle = curl_init();
        if (false === $handle) {
            throw new ClientException('Unable to initialize a cURL session');
        }

        $options = $this->padCurlOptions($request);
        if (false === curl_setopt_array($handle, $options)) {
            throw new ClientException('Unable to configure a cURL session');
        }

        $result = curl_exec($handle);
        if (false === $result) {
            throw new NetworkException($request, curl_error($handle), curl_errno($handle));
        }

        $responseStatusCode = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $responseHeadersPartSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $responseHeadersPart = substr($result, 0, $responseHeadersPartSize);
        $responseBodyPart = substr($result, $responseHeadersPartSize);

        $response = $this->responseFactory->createResponse($responseStatusCode)
        ->withBody($this->streamFactory->createStream($responseBodyPart));

        foreach (explode("\n", $responseHeadersPart) as $header) {
            $colonPosition = strpos($header, ':');

            if (false === $colonPosition) { // Status Line
                continue;
            } elseif (0 === $colonPosition) { // HTTP/2 Field
                continue;
            }

            list($name, $value) = explode(':', $header, 2);
            $response = $response->withAddedHeader(trim($name), trim($value));
        }

        curl_close($handle);
        return $response;
    }

    /**
     * Supplements options for a cURL session from the given request message
     *
     * @param RequestInterface $request
     *
     * @return array
     */
    protected function padCurlOptions(RequestInterface $request) : array
    {
        $options = $this->curlOptions;

        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_HEADER]         = true;
        $options[CURLOPT_CUSTOMREQUEST]  = $request->getMethod();
        $options[CURLOPT_URL]            = (string) $request->getUri();
        $options[CURLOPT_POSTFIELDS]     = (string) $request->getBody();

        foreach ($request->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $header = sprintf('%s: %s', $name, $value);
                $options[CURLOPT_HTTPHEADER][] = $header;
            }
        }

        return $options;
    }
}
