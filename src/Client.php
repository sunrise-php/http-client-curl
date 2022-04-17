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
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
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
use function curl_multi_add_handle;
use function curl_multi_close;
use function curl_multi_exec;
use function curl_multi_init;
use function curl_multi_remove_handle;
use function curl_setopt_array;
use function explode;
use function in_array;
use function ltrim;
use function sprintf;
use function strpos;
use function substr;

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
 * HTTP client based on cURL
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
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var array
     */
    protected $curlOptions;

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
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request) : ResponseInterface
    {
        $curlHandle = $this->createCurlHandleFromRequest($request);

        $isSuccess = curl_exec($curlHandle);
        if ($isSuccess === false) {
            throw new NetworkException($request, curl_error($curlHandle), curl_errno($curlHandle));
        }

        $response = $this->createResponseFromCurlHandle($curlHandle);

        curl_close($curlHandle);

        return $response;
    }

    /**
     * Sends the given requests and returns responses in the same order
     *
     * @param RequestInterface ...$requests
     *
     * @return ResponseInterface[]
     *
     * @throws ClientException
     * @throws NetworkException
     */
    public function sendRequests(RequestInterface ...$requests) : array
    {
        $curlMultiHandle = curl_multi_init();
        if ($curlMultiHandle === false) {
            throw new ClientException('Unable to create CurlMultiHandle');
        }

        $curlHandles = [];
        foreach ($requests as $i => $request) {
            $curlHandles[$i] = $this->createCurlHandleFromRequest($request);
            curl_multi_add_handle($curlMultiHandle, $curlHandles[$i]);
        }

        do {
            curl_multi_exec($curlMultiHandle, $isActive);
        } while ($isActive);

        $responses = [];
        foreach ($curlHandles as $i => $curlHandle) {
            $responses[$i] = $this->createResponseFromCurlHandle($curlHandle);
            curl_multi_remove_handle($curlMultiHandle, $curlHandle);
            curl_close($curlHandle);
        }

        curl_multi_close($curlMultiHandle);

        return $responses;
    }

    /**
     * Creates a CurlHandle from the given request
     *
     * @param RequestInterface $request
     *
     * @return resource
     *
     * @throws ClientException
     */
    private function createCurlHandleFromRequest(RequestInterface $request)
    {
        $curlOptions = $this->curlOptions;

        $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $curlOptions[CURLOPT_HEADER]         = true;

        $curlOptions[CURLOPT_CUSTOMREQUEST]  = $request->getMethod();
        $curlOptions[CURLOPT_URL]            = (string) $request->getUri();

        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            $curlOptions[CURLOPT_POSTFIELDS] = (string) $request->getBody();
        }

        $curlOptions[CURLOPT_HTTPHEADER] = [];
        foreach ($request->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $curlOptions[CURLOPT_HTTPHEADER][] = sprintf('%s: %s', $name, $value);
            }
        }

        $curlHandle = curl_init();
        if ($curlHandle === false) {
            throw new ClientException('Unable to create CurlHandle');
        }

        $isSuccess = curl_setopt_array($curlHandle, $curlOptions);
        if ($isSuccess === false) {
            throw new ClientException('Unable to configure CurlHandle');
        }

        return $curlHandle;
    }

    /**
     * Creates a response from the given CurlHandle
     *
     * @param resource $curlHandle
     *
     * @return ResponseInterface
     */
    private function createResponseFromCurlHandle($curlHandle) : ResponseInterface
    {
        /** @var int */
        $statusCode = curl_getinfo($curlHandle, CURLINFO_RESPONSE_CODE);
        $response = $this->responseFactory->createResponse($statusCode);

        /** @var float */
        $totalTime = curl_getinfo($curlHandle, CURLINFO_TOTAL_TIME);
        $response = $response->withAddedHeader('X-Request-Time', sprintf('%.3f ms', $totalTime * 1000));

        /** @var ?string */
        $message = curl_multi_getcontent($curlHandle);
        if ($message === null) {
            return $response;
        }

        /** @var int */
        $headerSize = curl_getinfo($curlHandle, CURLINFO_HEADER_SIZE);

        $header = substr($message, 0, $headerSize);
        $response = $this->populateResponseWithHeaderFields($response, $header);

        $body = substr($message, $headerSize);
        $response->getBody()->write($body);

        return $response;
    }

    /**
     * Populates the given response with the given header's fields
     *
     * @param ResponseInterface $response
     * @param string $header
     *
     * @return ResponseInterface
     *
     * @link https://datatracker.ietf.org/doc/html/rfc2616#section-4.2
     */
    private function populateResponseWithHeaderFields(ResponseInterface $response, string $header) : ResponseInterface
    {
        $fields = explode("\r\n", $header);

        foreach ($fields as $field) {
            // status line
            if (0 === strpos($field, 'HTTP/')) {
                continue;
            }

            // HTTP/2 field
            if (0 === strpos($field, ':')) {
                continue;
            }

            // end...
            if ('' === $field) {
                continue;
            }

            [$name, $value] = explode(':', $field, 2);

            $response = $response->withAddedHeader($name, ltrim($value));
        }

        return $response;
    }
}
