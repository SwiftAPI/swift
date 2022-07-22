<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\GraphQl\Schema\Generator;

use GraphQL\Type\Definition\Type;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\Schema\Builder\Builder;
use Swift\GraphQl\Schema\Builder\ObjectBuilder;
use Swift\GraphQl\Schema\Registry;

#[Autowire]
class UserMeGenerator implements \Swift\GraphQl\Schema\Generator\GeneratorInterface {
    
    public function __construct(
        protected \Swift\Security\User\GraphQl\Executor\UserResolver $userResolver,
    ) {
    }
    
    public function generate( \Swift\GraphQl\Schema\Registry $registry ): \Swift\GraphQl\Schema\Registry {
        $registry->extendType( 'Query', function ( ObjectBuilder $objectBuilder ) use ( $registry ) {
            
            $objectBuilder->addField( 'UserMe', [
                'type'    => static fn() => Registry::$typeMap[ 'SecurityUser' ],
                'description' => 'Fetch the current user',
                'resolve' => function ( $objectValue, $args, $context, $info ) {
                    return $this->userResolver->resolveCurrentUser( $objectValue, $args, $context, $info );
                },
            ] );
            
        } );
        
        return $registry;
    }
    
}