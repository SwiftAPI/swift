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
class ForgotPasswordGenerator implements \Swift\GraphQl\Schema\Generator\GeneratorInterface {
    
    public function __construct(
        protected \Swift\Security\User\GraphQl\Executor\UserResolver $userResolver,
    ) {
    }
    
    public function generate( \Swift\GraphQl\Schema\Registry $registry ): \Swift\GraphQl\Schema\Registry {
        $registry->extendType( 'Mutation', function ( ObjectBuilder $objectBuilder ) use ( $registry ) {
            $response = $this->generateForgotPasswordResult( $registry );
            
            $objectBuilder->addField( 'UserForgotPassword', [
                'type'    => static fn() => Registry::$typeMap[ $response->getName() ],
                'description' => 'Forgot password request',
                'args'    => [
                    'email' => [
                        'type' => Type::string(),
                    ],
                ],
                'resolve' => function ( $objectValue, $args, $context, $info ) {
                    return $this->userResolver->resolveForgotPassword( $objectValue, $args, $context, $info );
                },
            ] );
            
        } );
        
        return $registry;
    }
    
    protected function generateForgotPasswordResult( Registry $registry ): ObjectBuilder {
        $object = Builder::objectType( 'ForgotPasswordResult' )
                         ->setDescription( 'Result of the forgot password mutation' )
                         ->addField( 'message', Builder::fieldType( 'message', Builder::nonNull( Type::string() ) )->setDescription( 'The message of the result' )->buildType() )
                         ->addField( 'code', Builder::fieldType( 'code', Builder::nonNull( Type::int() ) )->setDescription( 'HTTP Response Code' )->buildType() );
        
        $registry->objectType( $object );
        
        return $object;
    }
    
}