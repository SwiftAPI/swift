<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;


use Swift\DependencyInjection\Attributes\DI;
use Swift\HttpFoundation\ParameterBag;

/**
 * Class RoutesBag
 * @package Swift\Router
 */
#[DI(autowire: false)]
class RoutesBag extends ParameterBag {

    /** @var RouteInterface[] */
    protected array $parameters;

    public function get( string $key, $default = null ): RouteInterface|null {
        return parent::get( $key, $default );
    }

}