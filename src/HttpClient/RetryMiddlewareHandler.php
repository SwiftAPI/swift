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
use Swift\DependencyInjection\Attributes\Autowire;

/**
 * Class RetryMiddlewareHandler
 * @package Swift\HttpClient
 */
#[Autowire]
class RetryMiddlewareHandler {

    /**
     * @var HttpClientRetryMiddlewareInterface[] $middlewares
     */
    private iterable $middlewares;

    /**
     * Listen for retry events
     *
     * @param int                    $attemptNumber  How many attempts have been tried for this particular request
     * @param float                  $delay          How long the client will wait before retrying the request
     * @param RequestInterface       $request        Request
     * @param array                  $options        Guzzle request options
     * @param ResponseInterface|null $response       Response (or NULL if response not sent; e.g. connect timeout)
     */
    public function handle( int $attemptNumber, float $delay, RequestInterface $request, array $options, ?ResponseInterface $response ): array {
        foreach ($this->middlewares as $middleware) {
            [$request, $options] = $middleware->handle(
                static function (RequestInterface $requestItem, array $optionsItem): array {
                    return [$requestItem, $optionsItem];
                },
                $attemptNumber,
                $delay,
                $request,
                $options,
                $response,
            );
        }

        return [$request, $options];
    }

    /**
     * @param iterable $middlewares
     */
    #[Autowire]
    public function setMiddlewares( #[Autowire(tag: 'httpclient.retry_middleware')] iterable $middlewares ): void {
        $middlewares = iterator_to_array( $middlewares );
        
        $this->middlewares = $middlewares;
    }

}