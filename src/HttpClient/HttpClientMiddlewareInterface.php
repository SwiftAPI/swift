<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpClient;

use Psr\Http\Message\RequestInterface;
use Swift\Kernel\Attributes\DI;

/**
 * Interface HttpClientMiddlewareInterface
 * @package Swift\HttpClient
 */
#[DI(tags: ['httpclient.middleware'])]
interface HttpClientMiddlewareInterface {

    public function handle( callable $handler, RequestInterface $request, array $options ): array;

}