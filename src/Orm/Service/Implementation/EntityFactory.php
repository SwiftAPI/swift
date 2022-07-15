<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Service\Implementation;


use Cycle\ORM\EntityProxyInterface;
use Cycle\ORM\Exception\ORMException;
use Cycle\ORM\Heap\HeapInterface;
use Cycle\ORM\Heap\Node;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select\LoaderInterface;
use Cycle\ORM\Service\EntityFactoryInterface;
use Cycle\ORM\Service\IndexProviderInterface;
use Cycle\ORM\Service\MapperProviderInterface;
use Cycle\ORM\Service\RelationProviderInterface;
use Swift\Orm\Entity\AbstractEntity;

final class EntityFactory implements EntityFactoryInterface {
    
    public function __construct(
        private readonly HeapInterface             $heap,
        private readonly SchemaInterface           $schema,
        private readonly MapperProviderInterface   $mapperProvider,
        private readonly RelationProviderInterface $relationProvider,
        private readonly IndexProviderInterface    $indexProvider,
    ) {
    }
    
    public function make(
        string $role,
        array  $data = [],
        int    $status = Node::NEW,
        bool   $typecast = false
    ): object {
        $role = $data[ LoaderInterface::ROLE_KEY ] ?? $role;
        unset( $data[ LoaderInterface::ROLE_KEY ] );
        // Resolved role
        $rRole  = $this->resolveRole( $role );
        $relMap = $this->relationProvider->getRelationMap( $rRole );
        $mapper = $this->mapperProvider->getMapper( $rRole );
        
        $castedData = $typecast ? $mapper->cast( $data ) : $data;
        
        if ( $status !== Node::NEW ) {
            // unique entity identifier
            $pk = $this->schema->define( $role, SchemaInterface::PRIMARY_KEY );
            if ( \is_array( $pk ) ) {
                $ids = [];
                foreach ( $pk as $key ) {
                    if ( ! isset( $data[ $key ] ) ) {
                        $ids = null;
                        break;
                    }
                    $ids[ $key ] = $data[ $key ];
                }
            } else {
                $ids = isset( $data[ $pk ] ) ? [ $pk => $data[ $pk ] ] : null;
            }
            
            if ( $ids !== null ) {
                $e = $this->heap->find( $rRole, $ids );
                
                if ( $e !== null ) {
                    $node = $this->heap->get( $e );
                    \assert( $node !== null );
                    
                    return $mapper->hydrate( $e, $relMap->init( $this, $node, $castedData ) );
                }
            }
        }
        
        $node = new Node( $status, $castedData, $rRole );
        $e    = $mapper->init( $data, $role );
        
        /** Entity should be attached before {@see RelationMap::init()} running */
        $this->heap->attach( $e, $node, $this->indexProvider->getIndexes( $rRole ) );
        
        return $mapper->hydrate( $e, $relMap->init( $this, $node, $castedData ) );
    }
    
    public function resolveRole( object|string $entity ): string {
        if ( \is_object( $entity ) ) {
            $node = $this->heap->get( $entity );
            if ( $node !== null ) {
                return $node->getRole();
            }
            
            $class = $entity::class;
            if ( ! $this->schema->defines( $class ) ) {
                $parentClass = get_parent_class( $entity );
                
                if ( $parentClass === false
                     || ! $entity instanceof EntityProxyInterface
                     || ! $this->schema->defines( $parentClass )
                ) {
                    throw new ORMException( "Unable to resolve role of `$class`." );
                }
                $class = $parentClass;
            }
            
            $entity = $class;
        }
        
        return $this->schema->resolveAlias( $entity ) ?? throw new ORMException( "Unable to resolve role `$entity`." );
    }
}
