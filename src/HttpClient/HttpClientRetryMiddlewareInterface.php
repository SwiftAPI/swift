<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Swift\DependencyInjection\Attributes\DI;

/**
 * Interface HttpClientRetryMiddlewareInterface
 * @package Swift\HttpClient
 */
#[DI(tags: ['httpclient.retry_middleware'])]
interface HttpClientRetryMiddlewareInterface {

    /**
     * Listen for retry events
     *
     * @param int                    $attemptNumber  How many attempts have been tried for this particular request
     * @param float                  $delay          How long the client will wait before retrying the request
     * @param RequestInterface       $request        Request
     * @param array                  $options        Guzzle request options
     * @param ResponseInterface|null $response       Response (or NULL if response not sent; e.g. connect timeout)
     */
    public function handle( callable $handler, int $attemptNumber, float $delay, RequestInterface $request, array $options, ?ResponseInterface $response ): array;

}