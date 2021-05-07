<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Application\Bootstrap\DependencyInjection;

use Exception;
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

        if (!file_exists(INCLUDE_DIR . '/services.yaml')) {
            throw new \RuntimeException('Missing services.yaml declaration in root. Please see https://swiftapi.github.io/swift-docs/docs/dependency-injection');
        }

        $loader = new YamlFileLoader($container, new FileLocator(INCLUDE_DIR));
        $loader->load('services.yaml');

        $container->compile();

        return $container;
    }
    
}