<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;

use Swift\HttpFoundation\ParameterBag;

/**
 * Class RouteParameterBag
 * @package Swift\Router
 */
final class RouteParameterBag extends ParameterBag {

    /** @var RouteParameter[] */
    protected array $parameters;

    public function get( string $key, $default = null ): RouteParameter|null {
        return parent::get( $key, $default );
    }

}