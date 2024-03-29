<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Controller;


use Swift\Configuration\ConfigurationInterface;
use Swift\GraphQl\Kernel\Middleware\RequestMiddleware;
use Swift\HttpFoundation\Exception\AccessDeniedException;
use Swift\HttpFoundation\JsonResponse;
use Swift\Router\Attributes\Route;
use Swift\Router\Types\RouteMethod;

class Index extends \Swift\Controller\AbstractController {
    
    public function __construct(
        protected \Swift\GraphQl\Kernel  $kernel,
        protected ConfigurationInterface $configuration,
    ) {
    }
    
    #[Route( method: [ RouteMethod::POST ], route: '/graphql/', name: 'graphql' )]
    public function index(): JsonResponse {
        if ( ! $this->configuration->get( 'graphql.enabled', 'app' ) ) {
            throw new AccessDeniedException( 'GraphQl is disabled' );
        }
        
        throw new \RuntimeException( 'GraphQl should not be called directly, but only functions as a backup so no 404 is generated. ' . RequestMiddleware::class .  ' is responsible for handling GraphQl requests.' );
    }
    
}