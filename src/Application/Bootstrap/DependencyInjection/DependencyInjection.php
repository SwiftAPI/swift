<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Application\Bootstrap\DependencyInjection;

use Exception;
use Swift\DependencyInjection\ContainerFactory;
use Swift\DependencyInjection\ContainerInterface;

/**
 * Class DependencyInjection
 * @package Swift\Application\Bootstrap\DependencyInjection
 */
class DependencyInjection {
    
    /**
     * Initialize DI
     *
     * @throws Exception
     */
    public function initialize(): ContainerInterface {
        return ContainerFactory::createContainer();
    }
    
}