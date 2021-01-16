<?php declare(strict_types=1);

namespace Swift\Router\Types;

use Swift\Kernel\Attributes\DI;
use Swift\Kernel\TypeSystem\Enum;

/**
 * Class RouteTypesEnum
 * @package Swift\Router\Types
 *
 * @method static RouteTypesEnum GET()
 * @method static RouteTypesEnum POST()
 * @method static RouteTypesEnum PUT()
 * @method static RouteTypesEnum PATCH()
 * @method static RouteTypesEnum DELETE()
 * @method static RouteTypesEnum HEAD()
 */
#[DI(exclude: true)]
class RouteTypesEnum extends Enum {

    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const PATCH = 'PATCH';
    public const DELETE = 'DELETE';
    public const HEAD = 'HEAD';

}