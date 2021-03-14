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
use Swift\Router\Types\RouteMethodEnum;
use Swift\Security\Authorization\Attributes\IsGranted;
use Swift\Security\Authorization\AuthorizationTypesEnum;

/**
 * Class User
 * @package Swift\Users\Controller
 */
#[Route(method: [RouteMethodEnum::POST, RouteMethodEnum::PUT], route: '/users/', name: 'users'), Autowire]
class User extends AbstractController {

    use ControllersAwareTrait;



}