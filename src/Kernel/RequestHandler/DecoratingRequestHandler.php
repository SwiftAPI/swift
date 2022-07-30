<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\RequestHandler;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DecoratingRequestHandler implements \Psr\Http\Server\RequestHandlerInterface {
    
    public function __construct(
        protected \Closure $closure,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function handle( ServerRequestInterface $request ): ResponseInterface {
        $closure = $this->closure;
        return $closure( $request );
    }
    
}