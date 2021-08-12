<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpClient\Middleware;


use Psr\Http\Message\RequestInterface;
use Swift\Configuration\ConfigurationInterface;
use Swift\HttpClient\HttpClientMiddlewareInterface;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class ConfigurationMiddleware
 * @package Swift\HttpClient\Middleware
 */
#[Autowire]
class ConfigurationMiddleware implements HttpClientMiddlewareInterface {

    /**
     * ConfigurationMiddleware constructor.
     */
    public function __construct(
        private ConfigurationInterface $configuration,
    ) {
    }

    public function handle( callable $handler, RequestInterface $request, array $options ): array {
        $options['verify'] = !$this->isDevMode();

        return $handler($request, $options);
    }

    private function isDevMode(): bool {
        return $this->configuration->get('app.debug', 'root') || ($this->configuration->get('app.mode', 'root') === 'develop');
    }
}