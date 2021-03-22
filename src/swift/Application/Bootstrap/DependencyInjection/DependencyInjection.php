<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Application\Bootstrap\DependencyInjection;

use Exception;
use Swift\Kernel\ServiceLocatorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Swift\Kernel\Container\Container;

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
    public function initialize(): Container {
        $container = new Container();
        $loader = new YamlFileLoader($container, new FileLocator(INCLUDE_DIR));
        $loader->load('services.yaml');
        $container->compile();

        return $container;
    }
    
}