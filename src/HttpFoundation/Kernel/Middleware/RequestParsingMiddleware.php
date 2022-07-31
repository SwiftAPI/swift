<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Kernel\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swift\HttpFoundation\ServerRequest;
use Swift\Kernel\Middleware\KernelMiddlewareOrder;
use Swift\Serializer\Json;

/**
 * Parse PSR Requests into /Swift/HttpFoundation/ServerRequest objects.
 * These objects implement the PSR-7 ServerRequestInterface, however they add some additional features.
 */
class RequestParsingMiddleware implements \Swift\Kernel\Middleware\MiddlewareInterface {
    
    /**
     * @inheritDoc
     */
    public function getOrder(): int {
        return KernelMiddlewareOrder::REQUEST_PARSING;
    }
    
    /**
     * @inheritDoc
     */
    public function process( ServerRequestInterface $request, RequestHandlerInterface $handler ): ResponseInterface {
        if ( $request instanceof ServerRequest ) {
            return $handler->handle( $request );
        }
        
        $body = $request->getParsedBody();
        if ( empty( $body ) || method_exists( $request, 'getBody' ) ) {
            $request->getBody()->rewind();
            $body = ( new Json( $request->getBody()->getContents() ) )->modeArray()->unSerialize();
        }
        
        return $handler->handle( new ServerRequest( $request->getQueryParams(), (array) $body, $request->getAttributes(), $request->getCookieParams(), $request->getUploadedFiles(), $request->getServerParams() ) );
    }
    
}