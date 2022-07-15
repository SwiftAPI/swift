<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection;

use Swift\FileSystem\FileSystem;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

$fileSystem = new FileSystem();

if ( ! $fileSystem->exists( '/var/cache/di/container.php' ) ) {
    class CachedContainer {
        
        public function __construct() {
            throw new \RuntimeException( 'Can not created cache container instance from non-existing cache' );
        }
        
    }
    
    return;
}

class CachedContainer extends \CachedContainer implements ContainerInterface {
    
    public function __construct() {
        parent::__construct();
        
        // Support deprecated usage of container global. Usage is highly discouraged. Use injection and compiler passes instead.
        global $container;
        $container     = $this;
        $this->aliases = $this->aliasMapping;
    }
    
    /**
     * Gets a service.
     *
     * @throws ServiceCircularReferenceException When a circular reference is detected
     * @throws ServiceNotFoundException          When the service is not defined
     * @throws \Exception                        if an exception has been thrown when the service has been resolved
     *
     * @see Reference
     */
    public function get( string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE ): ?object {
        try {
            return parent::get( $id, $invalidBehavior );
        } catch ( \Exception ) {
        }
        
        if ( array_key_exists( $id, $this->aliasMapping ) ) {
            
            return $this->{$this->methodMap[ $id ]}();
        }
        
        throw new ServiceNotFoundException( $id );
    }
    
    /**
     * @inheritDoc
     */
    public function getServicesByTag( string $tag ): array {
        $result = $this->tagsMapping[ $tag ] ?? [];
        
        if ( ! empty( $result ) ) {
            return $result;
        }
        
        return $this->aliasMapping[ $tag ] ? [ $this->aliasMapping[ $tag ] ] : [];
    }
    
    /**
     * @inheritDoc
     */
    public function getServiceInstancesByTag( string $tag ): array {
        $instances = [];
        
        if ( empty( $this->getServicesByTag( $tag ) ) ) {
            return $instances;
        }
        
        foreach ( $this->getServicesByTag( $tag ) as $className ) {
            if ( $this->has( $className ) ) {
                $instances[] = $this->get( $className );
            }
        }
        
        if ( array_key_exists( $tag, $this->aliasMapping ) ) {
            $instances[] = $this->get( $this->aliasMapping[ $tag ] );
        }
        
        return $instances;
    }
    
    /**
     * @inheritDoc
     */
    public function getResourcePaths(): array {
        return $this->resourcePathsMapping;
    }
    
    public function getReflectionClass( ?string $class, bool $throw = true ): ?\ReflectionClass {
        return $this->getReflectionFactoryService()->getReflectionClass( $class );
    }
    
    
}