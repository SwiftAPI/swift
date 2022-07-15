<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Tests\Schema\Middleware;


use Swift\GraphQl\Schema\Registry;

class UserCredentialsMiddleware implements \Swift\GraphQl\Schema\Middleware\SchemaMiddlewareInterface {
    
    /**
     * @inheritDoc
     */
    public function process( mixed $builder, Registry $registry, callable $next ): mixed {
        if ( ! $builder instanceof \Swift\GraphQl\Schema\Builder\ObjectBuilder ) {
            return $next( $builder, $registry );
        }
        
        if ( $builder->getName() !== 'SecurityUsersCredential' ) {
            return $next( $builder, $registry );
        }
        
        $fields = $builder->getFields();
        
        if ( ! is_array( $fields ) ) {
            return $next( $builder, $registry );
        }
        
        // Anonymize the password field
//        $fields['credential']['resolve'] = static function() {
//            return '##############';
//        };
        
        $builder->setFields( $fields );
    
        return $next( $builder, $registry );
    }
    
}