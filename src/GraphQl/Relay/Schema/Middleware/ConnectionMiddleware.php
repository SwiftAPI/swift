<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Relay\Schema\Middleware;


use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\Relay\Relay;
use Swift\GraphQl\Schema\Builder\Builder;
use Swift\GraphQl\Schema\Builder\ConnectionBuilder;
use Swift\GraphQl\Schema\Builder\FieldBuilder;
use Swift\GraphQl\Schema\Builder\ObjectBuilder;
use Swift\GraphQl\Schema\NamingStrategy;
use Swift\GraphQl\Schema\Registry;
use Swift\Orm\Dbal\ResultCollection;

#[Autowire]
class ConnectionMiddleware implements \Swift\GraphQl\Schema\Middleware\SchemaMiddlewareInterface {
    
    public function __construct(
        protected readonly NamingStrategy $namingStrategy,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function process( mixed $builder, Registry $registry, callable $next ): mixed {
        if ( ! $builder instanceof \Swift\GraphQl\Schema\Builder\ConnectionBuilder ) {
            return $next( $builder, $registry );
        }
        
        $interfaces = $builder->getInterfaces();
        
        if ( ! isset( $interfaces[ Relay::CONNECTION ] ) ) {
            return $next( $builder, $registry );
        }
        
        $edgeType = $this->createEdgeType( $builder, $registry )->buildType();
        
        /** @var ConnectionBuilder $builder */
        $builder
            ->addField(
                'edges',
                FieldBuilder::create(
                    'edges',
                    Type::listOf( $edgeType ),
                )->setDescription( 'The edges of the connection.' )
                            ->setResolver( static function ( $object, array $args, $context, ResolveInfo $info ) use ( $builder ) {
                                return array_map(
                                    static function ( $node ) use ( $builder ) {
                                        return [
                                            'cursor' => Relay::encodeId( $builder->getBuilder()->getName(), $node->id ),
                                            'node'   => $node,
                                        ];
                                    },
                                    (array) $object,
                                );
                            } )
                            ->buildType()
            )
            ->addField(
                'nodes',
                FieldBuilder::create(
                    'nodes',
                    static fn() => Type::listOf( Registry::$typeMap[ $builder->getBuilder()->getName() ] ),
                )->setDescription( 'The nodes of the connection.' )->buildType()
            )
            ->addField(
                'pageInfo',
                FieldBuilder::create(
                    'pageInfo',
                    static fn() => Type::nonNull( Registry::$typeMap[ Relay::PAGE_INFO ] ),
                )->setResolver( static function( ResultCollection $object, $args, $context, ResolveInfo $info ) use ( $builder ) {
                    $pageInfo = $object->getPageInfo();
                    
                    return [
                        'endCursor' => Relay::encodeId( $builder->getBuilder()->getName(), $pageInfo->getEndId() ),
                        'hasNextPage' => $pageInfo->hasNextPage(),
                        'hasPreviousPage' => $pageInfo->hasPreviousPage(),
                        'startCursor' => Relay::encodeId( $builder->getBuilder()->getName(), $pageInfo->getStartId() ),
                        'totalCount' => $pageInfo->getTotalCount(),
                    ];
                })->setDescription( 'Information to aid in pagination.' )->buildType()
            );
        
        
        return $next( $builder, $registry );
    }
    
    protected function createEdgeType( ConnectionBuilder $builder, Registry $registry ): ObjectBuilder {
        return Builder::objectType( $this->namingStrategy->edgeName( $builder->getName() ) )
                      ->addInterface( Relay::EDGE, static fn() => Registry::$typeMap[ Relay::EDGE ] )
                      ->addField(
                          'cursor',
                          FieldBuilder::create(
                              'cursor',
                              Type::nonNull( Type::string() ),
                          )->setDescription( 'A cursor for use in pagination.' )
                                      ->setResolver( static function ( $object, array $args, $context, ResolveInfo $info ) use ( $builder ) {
                                            return $object['cursor'];
                                      } )->buildType(),
                      )
                      ->addField(
                          'node',
                          FieldBuilder::create(
                              'node',
                              static fn() => Registry::$typeMap[ $builder->getBuilder()->getName() ],
                          )->setResolver( static function( $object) {
                              return $object['node'];
                          })->setDescription( 'The node of the edge' )->buildType(),
                      );
    }
    
}