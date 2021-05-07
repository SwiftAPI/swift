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
use Swift\Kernel\Attributes\DI;

/**
 * Class RouteTagBag
 * @package Swift\Router
 */
#[DI(autowire: false)]
class RouteTagBag extends ParameterBag {

    /**
     * RouteTagBag constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = array()) {
        $formatted = array();
        foreach ($parameters as $parameter) {
            $formatted[$parameter] = $parameter;
        }

        parent::__construct($formatted);
    }

    public function set( string $key, $value = null ): void {
        $value ??= $key;
        parent::set( $key, $value );
    }

}