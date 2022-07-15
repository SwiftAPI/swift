<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection;


use Swift\Configuration\Configuration;
use Swift\Configuration\Utils;
use Swift\DependencyInjection\Exception\MissingConfigurationException;
use Swift\FileSystem\FileSystem;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContainerFactory {
    
    public static function createContainer(): ContainerInterface {
        $fileSystem = new FileSystem();
    
        if ( $fileSystem->fileExists( '/var/cache/di/container.php' ) ) {
            require_once INCLUDE_DIR . '/var/cache/di/container.php';
        
            $container     = new \Swift\DependencyInjection\CachedContainer();
            $configuration = $container->get( Configuration::class );
        
            if ( Utils::isCacheEnabled( $configuration ) ) {
                return $container;
            }
        }
        
        $container = new Container();
    
        if ( ! file_exists( SWIFT_ROOT . '/services.yaml' ) ) {
            throw new MissingConfigurationException( 'Missing services.yaml declaration in SWIFT. This is important. This shouldn\'t occur. Could you try importing it in the root services declaration?');
        }
        
        $swiftLoader = new YamlFileLoader( $container, new FileLocator( SWIFT_ROOT ) );
        $swiftLoader->load( 'services.yaml' );
    
        if ( ! file_exists( INCLUDE_DIR . '/services.yaml' ) ) {
            throw new MissingConfigurationException( 'Missing services.yaml declaration in root. Please see https://swiftapi.github.io/swift-docs/docs/dependency-injection' );
        }
    
        $loader = new YamlFileLoader( $container, new FileLocator( INCLUDE_DIR ) );
        $loader->load( 'services.yaml' );
    
        $container->compile();
        
        return $container;
    }
    
}