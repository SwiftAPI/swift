<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Types;

use Swift\DependencyInjection\Attributes\DI;
use Swift\Kernel\TypeSystem\Enum;

/**
 * Class RouteMethod
 * @package Swift\Router\Types
 *
 * @method static RouteMethod GET()
 * @method static RouteMethod POST()
 * @method static RouteMethod PUT()
 * @method static RouteMethod PATCH()
 * @method static RouteMethod DELETE()
 * @method static RouteMethod HEAD()
 */
enum RouteMethod: string {

    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case HEAD = 'HEAD';
    case OPTIONS = 'OPTIONS';
    case CONNECT = 'CONNECT';
    case TRACE = 'TRACE';

}