<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Authorization\Controller;

use JetBrains\PhpStorm\Pure;
use OAuth2\Request;
use Swift\Authorization\Service\OauthService;
use Swift\Controller\AbstractController;
use Swift\HttpFoundation\JsonResponse;
use Swift\Kernel\Attributes\Autowire;
use Swift\Router\Attributes\Route;
use Swift\Router\RouteParameter;
use Swift\Router\Types\RouteMethodEnum;

/**
 * Class Oauth
 * @package Swift\Authorization\Controller
 */
#[Autowire]
class Oauth extends AbstractController {

//    /**
//     * Oauth constructor.
//     *
//     * @param OauthService $oauthService
//     */
//    #[Pure] #[Route(method: [RouteMethodEnum::POST], route: '/auth/', name: 'authorization.oauth')]
//    public function __construct(
//        protected OauthService $oauthService,
//    ) {
//    }
//
//    /**
//     * @param RouteParameter[] $params
//     *
//     * @return JsonResponse
//     *
//     * @see https://bshaffer.github.io/oauth2-server-php-docs/cookbook/
//     */
//    #[Route( method: [RouteMethodEnum::POST], route: '/token/', name: 'auth.token' )]
//    public function token( array $params ): JsonResponse {
//        return new JsonResponse(data: $this->oauthService->server->handleTokenRequest(Request::createFromGlobals())->getResponseBody(), json: true);
//    }
//

}