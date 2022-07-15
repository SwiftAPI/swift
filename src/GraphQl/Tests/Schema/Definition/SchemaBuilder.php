<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Tests\Schema\Definition;


use GraphQL\Type\Definition\Type;
use Swift\GraphQl\Schema\Builder\Builder;
use Swift\GraphQl\Schema\Registry;

class SchemaBuilder implements \Swift\GraphQl\Schema\Definition\SchemaBuilderInterface {
    
    public function define( Registry $registry ): Registry {
        $objectType = Builder::objectType( 'Greeting' )
                             ->setDescription( 'Greeting to user' )
                             ->setFields(
                                 [
                                     Builder::fieldType( 'greeting', Type::string() )
                                            ->buildType(),
                                 ],
                             );
        
        $registry->objectType( $objectType );
        
        $registry->extendType( 'Query', function ( $type ) use ( $objectType ) {
            $type->addField( 'SayHello', static function () use ( $objectType ) {
                return Builder::fieldType( 'SayHello', Registry::$typeMap[ $objectType->getName() ] )
                              ->setDescription( 'Says hello' )
                              ->addArgument( 'name', Type::string() )
                              ->setResolver( function ( $value, $args ) {
                                  return [
                                      'greeting' => sprintf( 'Hello %s!', $args[ 'name' ] ),
                                  ];
                              } )
                              ->build();
            } );
            
            return $type;
        } );
        
        
        return $registry;
    }
    
}