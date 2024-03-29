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
     * @var array<int, mixed>
     */
    protected $curlOptions;

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param array<int, mixed> $curlOptions
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
     * @return array<int, ResponseInterface>
     *
     * @throws ClientException
     * @throws NetworkException
     */
    public function sendRequests(RequestInterface ...$requests) : array
    {
        /** @var list<RequestInterface> $requests */

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
        $curlOptions[CURLOPT_HEADER] = true;

        $curlOptions[CURLOPT_CUSTOMREQUEST] = $request->getMethod();
        $curlOptions[CURLOPT_URL] = $request->getUri()->__toString();

        $curlOptions[CURLOPT_HTTPHEADER] = [];
        foreach ($request->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $curlOptions[CURLOPT_HTTPHEADER][] = sprintf('%s: %s', $name, $value);
            }
        }

        $curlOptions[CURLOPT_POSTFIELDS] = null;
        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            $curlOptions[CURLOPT_POSTFIELDS] = $request->getBody()->__toString();
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
        $requestTime = curl_getinfo($curlHandle, CURLINFO_TOTAL_TIME);
        $response = $response->withAddedHeader('X-Request-Time', sprintf('%.3f ms', $requestTime * 1000));

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
     * @link https://datatracker.ietf.org/doc/html/rfc7230#section-3.2
     */
    private function populateResponseWithHeaderFields(ResponseInterface $response, string $header) : ResponseInterface
    {
        $fields = explode("\r\n", $header);

        foreach ($fields as $i => $field) {
            // The first line of a response message is the status-line, consisting
            // of the protocol version, a space (SP), the status code, another
            // space, a possibly empty textual phrase describing the status code,
            // and ending with CRLF.
            // https://datatracker.ietf.org/doc/html/rfc7230#section-3.1.2
            if ($i === 0) {
                continue;
            }

            // All HTTP/1.1 messages consist of a start-line followed by a sequence
            // of octets in a format similar to the Internet Message Format:
            // zero or more header fields (collectively referred to as
            // the "headers" or the "header section"), an empty line indicating the
            // end of the header section, and an optional message body.
            // https://datatracker.ietf.org/doc/html/rfc7230#section-3
            // https://datatracker.ietf.org/doc/html/rfc5322
            if ($field === '') {
                break;
            }

            // While HTTP/1.x used the message start-line (see [RFC7230],
            // Section 3.1) to convey the target URI, the method of the request, and
            // the status code for the response, HTTP/2 uses special pseudo-header
            // fields beginning with ':' character (ASCII 0x3a) for this purpose.
            // https://datatracker.ietf.org/doc/html/rfc7540#section-8.1.2.1
            if ($field[0] === ':') {
                continue;
            }

            [$name, $value] = explode(':', $field, 2);

            $response = $response->withAddedHeader($name, ltrim($value));
        }

        return $response;
    }
}
