<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Controller;

use Swift\Controller\AbstractController;
use Swift\HttpFoundation\JsonResponse;
use Swift\Router\Attributes\Route;
use Swift\Router\Types\RouteMethodEnum;

/**
 * Class AuthenticationControllerRest
 * @package Swift\Security\Authentication\Controller
 */
#[Route( method: [RouteMethodEnum::POST], route: '/auth/', name: 'authorize' )]
class AuthenticationControllerRest extends AbstractController {

    /**
     * Create authentication- and refresh token based on client credentials
     *
     * @return JsonResponse
     */
    #[Route(method: [RouteMethodEnum::POST], route: '/token/', name: 'authorize.token', tags: [Route::TAG_ENTRYPOINT])]
    public function token(): JsonResponse {
        return new JsonResponse([
            'access_token' => $this->getSecurityToken()->getTokenString(),
            'expires' => $this->getSecurityToken()->expiresAt()->format('Y-m-d H:i:s'),
            'token_type' => 'bearer',
            'refresh_token' => $this->getSecurityToken()->getRefreshToken()->getTokenString(),
        ]);
    }

    #[Route(method: [RouteMethodEnum::POST], route: '/token/refresh', name: 'authorize.refresh_token', tags: [Route::TAG_ENTRYPOINT])]
    public function refreshToken(): JsonResponse {

    }

}