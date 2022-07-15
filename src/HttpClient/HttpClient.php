<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpClient;


use GuzzleHttp\Client;
use Swift\DependencyInjection\Attributes\DI;

/**
 * Class HttpClient
 * @package Swift\HttpClient
 */
#[DI(tags: ['httpclient.client'])]
class HttpClient extends Client implements HttpClientInterface {

}