<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Relay\Schema;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Swift\GraphQl\Relay\Relay;
use Swift\GraphQl\Schema\Builder\Builder;
use Swift\GraphQl\Schema\Builder\ObjectBuilder;
use Swift\GraphQl\Schema\Generator\GeneratorInterface;
use Swift\GraphQl\Schema\Generator\ManualGeneratorInterface;
use Swift\GraphQl\Schema\Registry;

/**
 * Adds root Query and Mutation types to the schema.
 */
class RelayBaseGenerator implements GeneratorInterface, ManualGeneratorInterface {
    
    public function generate( \Swift\GraphQl\Schema\Registry $registry ): \Swift\GraphQl\Schema\Registry {
        $this->addNodeInterface( $registry );
        $this->addEdgeInterface( $registry );
        $this->addConnectionInterface( $registry );
        $this->addPageInfo( $registry );
        $this->addNode( $registry );
        
        return $registry;
    }
    
    protected function addNodeInterface( \Swift\GraphQl\Schema\Registry $registry ): void {
        $nodeInterface = Builder::interface( Relay::NODE )
                                ->addField(
                                    'id',
                                    Builder::fieldType( 'id', Type::nonNull( Type::id() ) )
                                           ->setDescription( 'ID of the Node' )
                                           ->buildType()
                                )
                                ->addField(
                                    'incrementId',
                                    Builder::fieldType( 'incrementId', Type::nonNull( Type::int() ) )
                                           ->setDescription( 'Incremental id of the Node' )
                                           ->buildType()
                                )
                                ->setDescription( 'An object with an ID' )
                                ->setResolveType( function ( $object, $args, $info ) {
                                    return $info->fieldDefinition->name;
                                } );
        
        $registry->interfaceType( $nodeInterface );
        Registry::$alias[ Relay::NODE ] = $nodeInterface->getName();
    }
    
    protected function addEdgeInterface( \Swift\GraphQl\Schema\Registry $registry ): void {
        $edgeInterface = Builder::interface( Relay::EDGE )
                                ->addField(
                                    'cursor',
                                    Builder::fieldType( 'cursor', Type::nonNull( Type::string() ) )
                                           ->setDescription( 'Cursor of the Edge' )
                                           ->buildType()
                                )
                                ->addField(
                                    'node',
                                    Builder::fieldType( 'node', static fn() => Registry::$typeMap[ Relay::NODE ] )
                                           ->setDescription( 'Nodes in the edge' )
                                           ->buildType()
                                )
                                ->setDescription( 'An edge in a connection' )
                                ->setResolveType( function ( $object ) {
            
                                    return $object->getType();
                                } );
        
        $registry->interfaceType( $edgeInterface );
        Registry::$alias[ Relay::EDGE ] = $edgeInterface->getName();
    }
    
    protected function addConnectionInterface( \Swift\GraphQl\Schema\Registry $registry ): void {
        $connectionInterface = Builder::interface( Relay::CONNECTION )
                                      ->addField(
                                          'edges',
                                          Builder::fieldType( 'edges', static fn() => Builder::listOf( Registry::$typeMap[ Relay::EDGE ] ) )
                                                 ->setDescription( 'Edges for the connection' )
                                                 ->buildType()
                                      )
                                      ->addField(
                                          'nodes',
                                          Builder::fieldType( 'nodes', static fn() => Type::listOf( Registry::$typeMap[ Relay::NODE ] ) )
                                                 ->setDescription( 'The nodes of the connection, without the edges' )
                                                 ->buildType()
                                      )
                                      ->addField(
                                          'pageInfo',
                                          Builder::fieldType( 'pageInfo', static fn() => Registry::$typeMap[ Relay::PAGE_INFO ] )
                                                 ->setDescription( 'Info about pagination of the connection' )
                                                 ->buildType()
                                      )
                                      ->setDescription( 'A connection to a list of items' )
                                      ->setResolveType( function ( $object ) {
            
                                          return $object->getType();
                                      } );
        
        $registry->interfaceType( $connectionInterface );
        Registry::$alias[ Relay::CONNECTION ] = $connectionInterface->getName();
    }
    
    protected function addPageInfo( \Swift\GraphQl\Schema\Registry $registry ): void {
        $pageInfoInterface = Builder::objectType( Relay::PAGE_INFO )
                                    ->addField(
                                        'endCursor',
                                        Builder::fieldType( 'endCursor', Type::string() )
                                               ->setDescription( 'When paginating forwards, the cursor to continue.' )
                                               ->setResolver( static function ( $object ) {
                                                   return $object[ 'endCursor' ];
                                               } )
                                               ->buildType()
                                    )
                                    ->addField(
                                        'hasNextPage',
                                        Builder::fieldType( 'hasNextPage', Type::nonNull( Type::boolean() ) )
                                               ->setDescription( 'When paginating forwards, are there more items?' )
                                               ->setResolver( static function ( $object ) {
                                                   return $object[ 'hasNextPage' ];
                                               } )
                                               ->buildType()
                                    )
                                    ->addField(
                                        'hasPreviousPage',
                                        Builder::fieldType( 'hasPreviousPage', Type::nonNull( Type::boolean() ) )
                                               ->setDescription( 'When paginating backwards, are there more items?' )
                                               ->setResolver( static function ( $object ) {
                                                   return $object[ 'hasPreviousPage' ];
                                               } )
                                               ->buildType()
                                    )
                                    ->addField(
                                        'startCursor',
                                        Builder::fieldType( 'startCursor', Type::string() )
                                               ->setDescription( 'When paginating backwards, the cursor to continue.' )
                                               ->setResolver( static function ( $object ) {
                                                   return $object[ 'startCursor' ];
                                               } )
                                               ->buildType()
                                    )
                                    ->addField(
                                        'totalCount',
                                        Builder::fieldType( 'totalCount', Type::string() )
                                               ->setDescription( 'Total number of items available' )
                                               ->setResolver( static function ( $object ) {
                                                   return $object[ 'totalCount' ];
                                               } )
                                               ->buildType()
                                    )
                                    ->setDescription( 'Pagination info for a connection' );
        
        $registry->objectType( $pageInfoInterface );
        Registry::$alias[ Relay::PAGE_INFO ] = $pageInfoInterface->getName();
    }
    
    protected function addNode( Registry $registry ): void {
        $registry->extendType( 'Query', static function ( ObjectBuilder $objectBuilder ) {
            $objectBuilder->addField(
                'node',
                Builder::fieldType( 'node', static fn() => Registry::$typeMap[ Relay::NODE ] )
                       ->setDescription( 'Fetches an object given its ID' )
                       ->addArgument(
                           'id',
                           Type::nonNull( Type::id() ),
                           'ID of the object to fetch'
                       )
                       ->setResolver( static function ( $root, $args, $context, $info ) {
                           return Relay::resolveNode( $root, $args, $context, $info );
                       } )->buildType()
            );
            
            return $objectBuilder;
        } );
    }
    
}