<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Users\Controller;

use Swift\Controller\AbstractController;
use Swift\HttpFoundation\JsonResponse;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Container\Provider\ControllersAwareTrait;
use Swift\Router\Attributes\Route;
use Swift\Router\Types\RouteTypesEnum;
use Swift\Security\Authorization\Attributes\IsGranted;
use Swift\Security\Authorization\AuthorizationRolesEnum;

/**
 * Class User
 * @package Swift\Users\Controller
 */
#[Route(type: [RouteTypesEnum::POST, RouteTypesEnum::PUT], route: '/users/', name: 'users'), Autowire]
class User extends AbstractController {

    use ControllersAwareTrait;

    #[Route( type: RouteTypesEnum::POST, route: '/[i:device_id]/create/[create|edit:action]/', name: 'users.create' )]
    #[IsGranted([AuthorizationRolesEnum::LOGGED_IN])]
    public function create( array $params ): JsonResponse {
        return new JsonResponse(['testing']);
    }

}