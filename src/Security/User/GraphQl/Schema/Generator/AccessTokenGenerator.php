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
class AccessTokenGenerator implements \Swift\GraphQl\Schema\Generator\GeneratorInterface {
    
    public function __construct(
        protected \Swift\Security\User\GraphQl\Executor\ClientResolver $clientResolver,
    ) {
    }
    
    public function generate( \Swift\GraphQl\Schema\Registry $registry ): \Swift\GraphQl\Schema\Registry {
        $registry->extendType( 'Mutation', function ( ObjectBuilder $objectBuilder ) use ( $registry ) {
            $accessTokenResponse = $this->generateAccessTokenResult( $registry );
            $refreshTokenResponse = $this->generateRefreshTokenResult( $registry );
            
            $objectBuilder->addField( 'AuthAccessTokenGet', [
                'type'    => static fn() => Registry::$typeMap[ $accessTokenResponse->getName() ],
                'description' => 'Get an access token as client',
                'args'    => [
                    'grantType' => [
                        'type' => Type::string(),
                    ],
                    'clientId' => [
                        'type' => Type::string(),
                    ],
                    'clientSecret' => [
                        'type' => Type::string(),
                    ],
                ],
                'resolve' => function ( $objectValue, $args, $context, $info ) {
                    return $this->clientResolver->resolveAuthTokenGet( $objectValue, $args, $context, $info );
                },
            ] );
    
            $objectBuilder->addField( 'AuthRefreshToken', [
                'type'    => static fn() => Registry::$typeMap[ $refreshTokenResponse->getName() ],
                'description' => 'Refresh an access token as client',
                'args'    => [
                    'grantType' => [
                        'type' => Type::string(),
                    ],
                    'refreshToken' => [
                        'type' => Type::string(),
                    ],
                ],
                'resolve' => function ( $objectValue, $args, $context, $info ) {
                    return $this->clientResolver->resolveRefreshTokenGet( $objectValue, $args, $context, $info );
                },
            ] );
            
        } );
        
        return $registry;
    }
    
    protected function generateAccessTokenResult( Registry $registry ): ObjectBuilder {
        $object = Builder::objectType( 'AccessToken' )
                         ->setDescription( 'Result of the forgot access token mutation' )
                         ->addField( 'accessToken', Builder::fieldType( 'accessToken', Builder::nonNull( Type::string() ) )->buildType() )
                         ->addField( 'expires', Builder::fieldType( 'expires', Builder::nonNull( TypeFactory::dateTime() ) )->buildType() )
                         ->addField( 'refreshToken', Builder::fieldType( 'refreshToken', Builder::nonNull( Type::string() ) )->buildType() )
                         ->addField( 'tokenType', Builder::fieldType( 'tokenType', Builder::nonNull( Type::string() ) )->buildType() );
        
        $registry->objectType( $object );
        
        return $object;
    }
    
    protected function generateRefreshTokenResult( Registry $registry ): ObjectBuilder {
        $object = Builder::objectType( 'RefreshToken' )
                         ->setDescription( 'Result of the forgot refresh token mutation' )
                         ->addField( 'accessToken', Builder::fieldType( 'accessToken', Builder::nonNull( Type::string() ) )->buildType() )
                         ->addField( 'expires', Builder::fieldType( 'expires', Builder::nonNull( TypeFactory::dateTime() ) )->buildType() )
                         ->addField( 'tokenType', Builder::fieldType( 'tokenType', Builder::nonNull( Type::string() ) )->buildType() );
        
        $registry->objectType( $object );
        
        return $object;
    }
    
}