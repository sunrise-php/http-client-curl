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

use CURLFile;

/**
 * @since 2.2.0
 */
final class File extends CURLFile
{
    public function __construct(string $filename, ?string $mediaType = null)
    {
        parent::__construct($filename, $mediaType);
    }
}
