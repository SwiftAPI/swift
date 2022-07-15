<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Controller;


use Swift\HttpFoundation\JsonResponse;
use Swift\Router\Attributes\Route;
use Swift\Router\Types\RouteMethod;

class Index extends \Swift\Controller\AbstractController {
    
    public function __construct(
        protected \Swift\GraphQl\Kernel $kernel,
    ) {
    }
    
    #[Route( method: [ RouteMethod::POST ], route: '/graphql/', name: 'graphql' )]
    public function index(): JsonResponse {
        return $this->kernel->run( $this->getRequest() );
    }
    
}