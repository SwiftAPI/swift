<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Generator;

use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Swift\GraphQl\Schema\Builder\Builder;
use Swift\GraphQl\Type\DateTimeWithPreFormat;

/**
 * Adds root Query and Mutation types to the schema.
 */
class BaseGenerator implements GeneratorInterface, ManualGeneratorInterface {
    
    public function run( \Swift\GraphQl\Schema\Registry $registry ): \Swift\GraphQl\Schema\Registry {
        $query = Builder::objectType( 'Query' )
                        ->setDescription( 'The root query type.' );
        $registry->objectType( $query );
        
        $mutation = Builder::objectType( 'Mutation' )
                           ->setDescription( 'The root mutation type.' );
        $registry->objectType( $mutation );
        
        $dateFormat = Builder::directive( 'formatDate' )
                             ->setDescription( 'Format a date.' )
                             ->addLocation( DirectiveLocation::FIELD )
                             ->addArgument( 'format', Type::string(), 'Date format template' )
                             ->setResolver( function ( mixed $value, mixed $args, mixed $context, ResolveInfo $info ) {
                                 if ( ! $value instanceof \DateTimeInterface ) {
                                     return ( new \DateTimeImmutable( $value ) )->format( $args[ 'format' ] );
                                 }
            
                                 return new DateTimeWithPreFormat( $value->format( \DateTimeImmutable::ATOM ), $args[ 'format' ] );
                             } );
        $registry->directive( $dateFormat );
        
        return $registry;
    }
    
}