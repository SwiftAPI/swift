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
use Swift\Kernel\Attributes\Autowire;

/**
 * Class MiddlewareHandler
 * @package Swift\HttpClient
 */
#[Autowire]
class MiddlewareHandler {

    /**
     * @var HttpClientMiddlewareInterface[] $middlewares
     */
    private iterable $middlewares;

    public function handle( RequestInterface $request, array $options ): array {
        foreach ($this->middlewares as $middleware) {
            [$request, $options] = $middleware->handle(
                static function (RequestInterface $requestItem, array $optionsItem) {
                    return [$requestItem, $optionsItem];
                },
                $request,
                $options,
            );
        }

        return [$request, $options];
    }

    /**
     * @param iterable $middlewares
     */
    #[Autowire]
    public function setMiddlewares( #[Autowire(tag: 'httpclient.middleware')] iterable $middlewares ): void {
        $this->middlewares = $middlewares;
    }

}