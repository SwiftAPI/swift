<?php declare(strict_types=1);

namespace Swift\Router\Types;

use Swift\Kernel\Attributes\DI;
use Swift\Kernel\TypeSystem\Enum;

/**
 * Class RouteMethodEnum
 * @package Swift\Router\Types
 *
 * @method static RouteMethodEnum GET()
 * @method static RouteMethodEnum POST()
 * @method static RouteMethodEnum PUT()
 * @method static RouteMethodEnum PATCH()
 * @method static RouteMethodEnum DELETE()
 * @method static RouteMethodEnum HEAD()
 */
#[DI(exclude: true)]
class RouteMethodEnum extends Enum {

    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const PATCH = 'PATCH';
    public const DELETE = 'DELETE';
    public const HEAD = 'HEAD';

}