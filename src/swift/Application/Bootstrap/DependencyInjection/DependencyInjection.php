<?php declare(strict_types=1);

namespace Swift\Application\Bootstrap\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Swift\Kernel\ContainerService\ContainerService;

class DependencyInjection {

    /**
     * Initialize DI
     * 
     * @throws Exception
     */
    public function initialize(): void {
        global $containerBuilder;
        $containerBuilder = new ContainerService();
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(INCLUDE_DIR));
        $loader->load('services.yaml');
        $containerBuilder->compile();
    }
    
}