<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Controller;

use Swift\Controller\AbstractController;
use Swift\HttpFoundation\JsonResponse;
use Swift\Router\Attributes\Route;
use Swift\Router\Types\RouteMethod;
use Swift\Security\Authentication\Token\TokenInterface;

/**
 * Class AuthenticationControllerRest
 * @package Swift\Security\Authentication\Controller
 */
#[Route( method: [RouteMethod::POST], route: '/auth/', name: 'authorize' )]
class AuthenticationController extends AbstractController {

    /**
     * Create authentication- and refresh token based on client credentials
     *
     * @return JsonResponse
     */
    #[Route(method: [RouteMethod::POST], route: '/token/', name: 'authorize.token', tags: [Route::TAG_ENTRYPOINT])]
    public function token(): JsonResponse {
        $response = [
            'access_token' => $this->getSecurityToken()->getTokenString(),
            'expires' => $this->getSecurityToken()->expiresAt()->format('Y-m-d H:i:s'),
            'token_type' => 'bearer',
        ];

        if ($this->getRequest()->getContent()->get('grant_type') === 'client_credentials') {
            $response['refresh_token'] = $this->getSecurityToken()->getRefreshToken()->getTokenString();
        }

        return new JsonResponse($response);
    }

}