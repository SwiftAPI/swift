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
use Swift\GraphQl\Type\TypeFactory;

#[Autowire]
class LoginGenerator implements \Swift\GraphQl\Schema\Generator\GeneratorInterface {
    
    public function __construct(
        protected \Swift\Security\User\GraphQl\Executor\UserResolver $userResolver,
    ) {
    }
    
    public function generate( \Swift\GraphQl\Schema\Registry $registry ): \Swift\GraphQl\Schema\Registry {
        $registry->extendType( 'Mutation', function ( ObjectBuilder $objectBuilder ) use ( $registry ) {
            $response = $this->createResponseType( $registry );
            
            $objectBuilder->addField( 'UserLogin', [
                'type'    => static fn() => Registry::$typeMap[ $response->getName() ],
                'args'    => [
                    'username' => [
                        'type' => Type::string(),
                    ],
                    'password' => [
                        'type' => Type::string(),
                    ],
                ],
                'resolve' => function ( $objectValue, $args, $context, $info ) {
                    return $this->userResolver->resolveLogin( $objectValue, $args, $context, $info );
                },
            ] );
        } );
        
        return $registry;
    }
    
    protected function createResponseType( \Swift\GraphQl\Schema\Registry $registry ): ObjectBuilder {
        $token = $this->createTokenType( $registry );
        
        $response = Builder::objectType( 'UserLoginResult' )
                           ->addField( 'user', static fn() => Registry::$typeMap[ 'SecurityUser' ] )
                           ->addField( 'token', static fn() => Registry::$typeMap[ $token->getName() ] );
        
        $registry->objectType( $response );
        
        return $response;
    }
    
    protected function createTokenType( \Swift\GraphQl\Schema\Registry $registry ): ObjectBuilder {
        $token = Builder::objectType( 'UserToken' )
                        ->setDescription( 'Token for Authenticated User' )
                        ->addField( 'token', Builder::fieldType( 'token', Builder::nonNull( Type::string() ) )->setDescription( 'Token string for Authenticated User' )->buildType() )
                        ->addField( 'expires', Builder::fieldType( 'expires', Builder::nonNull( TypeFactory::dateTime() ) )->setDescription( 'Expiration of the token' )->buildType() );
        
        $registry->objectType( $token );
        
        return $token;
    }
    
    
}