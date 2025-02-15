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

namespace Sunrise\Http\Client\Curl;

use CurlHandle;
use CurlMultiHandle;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Sunrise\Http\Client\Curl\Exception\ClientException;
use Sunrise\Http\Client\Curl\Exception\NetworkException;

use function curl_close;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_multi_add_handle;
use function curl_multi_close;
use function curl_multi_exec;
use function curl_multi_getcontent;
use function curl_multi_init;
use function curl_multi_remove_handle;
use function curl_multi_select;
use function curl_setopt_array;
use function explode;
use function in_array;
use function ltrim;
use function sprintf;
use function strpos;
use function substr;
use function usleep;

use const CURLINFO_HEADER_SIZE;
use const CURLINFO_RESPONSE_CODE;
use const CURLINFO_TOTAL_TIME;
use const CURLM_CALL_MULTI_PERFORM;
use const CURLM_OK;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_HEADER;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_URL;

final class Client implements ClientInterface
{
    private const BODYLESS_HTTP_METHODS = ['HEAD', 'GET'];
    private const DEFAULT_CURL_MULTI_SELECT_TIMEOUT = 1.0;
    private const DEFAULT_CURL_MULTI_SELECT_SLEEP_DURATION = 1000;
    private const REQUEST_TIME_HEADER_FIELD_NAME = 'X-Request-Time';
    private const HEADER_FIELD_SEPARATOR = "\r\n";

    private ?CurlMultiHandle $curlMultiHandle = null;

    /**
     * @var array<array-key, CurlHandle>
     */
    private array $curlHandles = [];

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        /** @var array<int, mixed> */
        private readonly array $curlOptions = [],
        private readonly ?float $curlMultiSelectTimeout = null,
        private readonly ?int $curlMultiSelectSleepDuration = null,
    ) {
    }

    public function __destruct()
    {
        $this->clear();
    }

    /**
     * @inheritDoc
     *
     * @return ($request is MultiRequest ? MultiResponse : ResponseInterface)
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->executeRequest($request);
        } finally {
            $this->clear();
        }
    }

    private function executeRequest(RequestInterface $request): ResponseInterface
    {
        return $request instanceof MultiRequest
            ? $this->executeMultiRequest($request)
            : $this->executeSingleRequest($request);
    }

    private function executeSingleRequest(RequestInterface $request): ResponseInterface
    {
        $curlHandle = $this->createCurlHandleFromRequest($request);

        $curlExecuteResult = curl_exec($curlHandle);
        if ($curlExecuteResult === false) {
            throw new NetworkException(
                $request,
                curl_error($curlHandle),
                curl_errno($curlHandle),
            );
        }

        return $this->createResponseFromCurlHandle($curlHandle);
    }

    private function executeMultiRequest(MultiRequest $multiRequest): MultiResponse
    {
        $this->curlMultiHandle = curl_multi_init();

        foreach ($multiRequest->getRequests() as $key => $request) {
            $curlHandle = $this->createCurlHandleFromRequest($request, $key);
            $curlMultiStatusCode = curl_multi_add_handle($this->curlMultiHandle, $curlHandle);
            ClientException::assertCurlMultiStatusCodeSame(CURLM_OK, $curlMultiStatusCode);
        }

        $curlMultiSelectTimeout = $this->curlMultiSelectTimeout ?? self::DEFAULT_CURL_MULTI_SELECT_TIMEOUT;
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $curlMultiSelectSleepDuration = $this->curlMultiSelectSleepDuration ?? self::DEFAULT_CURL_MULTI_SELECT_SLEEP_DURATION;

        do {
            $curlMultiStatusCode = curl_multi_exec($this->curlMultiHandle, $isCurlMultiExecuteStillRunning);
            // https://stackoverflow.com/questions/19490837/curlm-call-multi-perform-deprecated
            if ($curlMultiStatusCode === CURLM_CALL_MULTI_PERFORM) {
                continue;
            }

            ClientException::assertCurlMultiStatusCodeSame(CURLM_OK, $curlMultiStatusCode);

            if ($isCurlMultiExecuteStillRunning) {
                $curlMultiSelectResult = curl_multi_select($this->curlMultiHandle, $curlMultiSelectTimeout);
                if ($curlMultiSelectResult === -1) {
                    // Take pauses to reduce CPU load...
                    usleep($curlMultiSelectSleepDuration);
                }
            }
        } while ($isCurlMultiExecuteStillRunning);

        $responses = [];
        foreach ($this->curlHandles as $key => $curlHandle) {
            $responses[$key] = $this->createResponseFromCurlHandle($curlHandle);
        }

        return new MultiResponse(...$responses);
    }

    private function createCurlHandleFromRequest(RequestInterface $request, int|string $key = 0): CurlHandle
    {
        $curlOptions = $this->curlOptions;

        $curlOptions[CURLOPT_CUSTOMREQUEST] = $request->getMethod();
        $curlOptions[CURLOPT_URL] = (string) $request->getUri();

        $curlOptions[CURLOPT_HTTPHEADER] = [];
        foreach ($request->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $curlOptions[CURLOPT_HTTPHEADER][] = sprintf('%s: %s', $name, $value);
            }
        }

        $curlOptions[CURLOPT_POSTFIELDS] = null;
        if (!in_array($request->getMethod(), self::BODYLESS_HTTP_METHODS, true)) {
            $curlOptions[CURLOPT_POSTFIELDS] = (string) $request->getBody();
        }

        $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $curlOptions[CURLOPT_HEADER] = true;

        $curlHandle = curl_init();
        if ($curlHandle === false) {
            throw new ClientException('Unable to create CurlHandle.');
        }

        $this->curlHandles[$key] = $curlHandle;

        $curlSetOptionsResult = curl_setopt_array($curlHandle, $curlOptions);
        if ($curlSetOptionsResult === false) {
            throw new ClientException('Unable to configure CurlHandle.');
        }

        return $curlHandle;
    }

    private function createResponseFromCurlHandle(CurlHandle $curlHandle): ResponseInterface
    {
        /** @var int $responseStatusCode */
        $responseStatusCode = curl_getinfo($curlHandle, CURLINFO_RESPONSE_CODE);
        if ($responseStatusCode === 0) {
            throw new ClientException(
                'Failed to retrieve response code. Please check the request and verify network accessibility.'
            );
        }

        $response = $this->responseFactory->createResponse($responseStatusCode);

        /** @var float $requestTime */
        $requestTime = curl_getinfo($curlHandle, CURLINFO_TOTAL_TIME);
        $formattedRequestTime = sprintf('%.3f ms', $requestTime * 1000);
        $response = $response->withAddedHeader(self::REQUEST_TIME_HEADER_FIELD_NAME, $formattedRequestTime);

        /** @var string $responseMessage */
        $responseMessage = curl_multi_getcontent($curlHandle);

        /** @var int $responseHeaderSize */
        $responseHeaderSize = curl_getinfo($curlHandle, CURLINFO_HEADER_SIZE);
        $responseHeader = substr($responseMessage, 0, $responseHeaderSize);
        $response = $this->populateResponseWithHeaderFields($response, $responseHeader);

        $responseContent = substr($responseMessage, $responseHeaderSize);
        $response->getBody()->write($responseContent);

        return $response;
    }

    private function populateResponseWithHeaderFields(ResponseInterface $response, string $header): ResponseInterface
    {
        $fields = explode(self::HEADER_FIELD_SEPARATOR, $header);

        foreach ($fields as $i => $field) {
            // https://datatracker.ietf.org/doc/html/rfc7230#section-3.1.2
            if ($i === 0) {
                continue;
            }

            // https://datatracker.ietf.org/doc/html/rfc7230#section-3
            // https://datatracker.ietf.org/doc/html/rfc5322
            if ($field === '') {
                break;
            }

            if (strpos($field, ':') === false) {
                continue;
            }

            /** @psalm-suppress PossiblyUndefinedArrayOffset */
            [$fieldName, $fieldValue] = explode(':', $field, 2);

            $response = $response->withAddedHeader($fieldName, ltrim($fieldValue));
        }

        return $response;
    }

    private function clear(): void
    {
        foreach ($this->curlHandles as $curlHandle) {
            if ($this->curlMultiHandle instanceof CurlMultiHandle) {
                curl_multi_remove_handle($this->curlMultiHandle, $curlHandle);
            }

            curl_close($curlHandle);
        }

        if ($this->curlMultiHandle instanceof CurlMultiHandle) {
            curl_multi_close($this->curlMultiHandle);
        }

        $this->curlMultiHandle = null;
        $this->curlHandles = [];
    }
}
