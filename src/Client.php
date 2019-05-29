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
use const CURLINFO_TOTAL_TIME;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_HEADER;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_URL;

/**
 * HTTP Client based on cURL
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
     * HTTP header name for request time
     *
     * @var string
     */
    protected const HTTP_HEADER_NAME_FOR_REQUEST_TIME = 'X-Request-Time';

    /**
     * Response Factory
     *
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * cURL options
     *
     * @var array
     */
    protected $curlOptions;

    /**
     * cURL handle
     *
     * @var null|resource
     */
    protected $curlHandle;

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param array $curlOptions
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        array $curlOptions = []
    ) {
        $this->responseFactory = $responseFactory;
        $this->curlOptions = $curlOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest(RequestInterface $request) : ResponseInterface
    {
        $this->curlInitialize();
        $this->curlConfigure($request);

        return $this->curlExecute($request);
    }

    /**
     * @return void
     *
     * @throws ClientException
     */
    protected function curlInitialize() : void
    {
        $this->curlHandle = curl_init();

        if (false === $this->curlHandle) {
            throw new ClientException('Unable to initialize cURL session');
        }
    }

    /**
     * @param RequestInterface $request
     *
     * @return void
     *
     * @throws ClientException
     */
    protected function curlConfigure(RequestInterface $request) : void
    {
        $curlOptions = $this->curlOptions;

        $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $curlOptions[CURLOPT_HEADER]         = true;
        $curlOptions[CURLOPT_CUSTOMREQUEST]  = $request->getMethod();
        $curlOptions[CURLOPT_URL]            = (string) $request->getUri();
        $curlOptions[CURLOPT_POSTFIELDS]     = (string) $request->getBody();

        foreach ($request->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $curlOptions[CURLOPT_HTTPHEADER][] = sprintf('%s: %s', $name, $value);
            }
        }

        $result = curl_setopt_array($this->curlHandle, $curlOptions);

        if (false === $result) {
            throw new ClientException('Unable to configure cURL session');
        }
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws NetworkException
     */
    protected function curlExecute(RequestInterface $request) : ResponseInterface
    {
        $result = curl_exec($this->curlHandle);

        if (false === $result) {
            throw new NetworkException($request, curl_error($this->curlHandle), curl_errno($this->curlHandle));
        }

        $metadata = [];
        $metadata[CURLINFO_TOTAL_TIME] = curl_getinfo($this->curlHandle, CURLINFO_TOTAL_TIME);
        $metadata[CURLINFO_RESPONSE_CODE] = curl_getinfo($this->curlHandle, CURLINFO_RESPONSE_CODE);
        $metadata[CURLINFO_HEADER_SIZE] = curl_getinfo($this->curlHandle, CURLINFO_HEADER_SIZE);

        $rawHeaders = substr($result, 0, $metadata[CURLINFO_HEADER_SIZE]);
        $rawBody = substr($result, $metadata[CURLINFO_HEADER_SIZE]);

        $response = $this->responseFactory->createResponse($metadata[CURLINFO_RESPONSE_CODE]);
        $response = $this->parseHeaders($rawHeaders, $response);
        $response->getBody()->write($rawBody);

        $response = $response->withAddedHeader(
            static::HTTP_HEADER_NAME_FOR_REQUEST_TIME,
            sprintf('%.3f ms', $metadata[CURLINFO_TOTAL_TIME] * 1000)
        );

        curl_close($this->curlHandle);
        $this->curlHandle = null;

        return $response;
    }

    /**
     * @param string $headers
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    protected function parseHeaders(string $headers, ResponseInterface $response) : ResponseInterface
    {
        foreach (explode("\n", $headers) as $header) {
            $colpos = strpos($header, ':');

            if (false === $colpos) { // Status Line
                continue;
            } elseif (0 === $colpos) { // HTTP/2 Field
                continue;
            }

            list($name, $value) = explode(':', $header, 2);

            $response = $response->withAddedHeader(trim($name), trim($value));
        }

        return $response;
    }
}
