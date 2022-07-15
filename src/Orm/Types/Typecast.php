<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Types;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\Attributes\DI;
use Swift\DependencyInjection\ServiceLocator;

#[Autowire, DI( tags: [ 'orm.annotated' ] )]
class Typecast implements \Cycle\ORM\Parser\CastableInterface, \Cycle\ORM\Parser\UncastableInterface {
    
    protected readonly TypeTransformer $transformer;
    private array $rules = [];
    
    public function __construct() {
        $this->transformer = ( new ServiceLocator() )->get( TypeTransformer::class );
    }
    
    /**
     * @inheritDoc
     */
    public function setRules( array $rules ): array {
        foreach ( $rules as $column => $rule ) {
            if ( ! $this->transformer->getType( $rule ) ) {
                continue;
            }
            
            $this->rules[ $column ] = $rule;
            unset( $rules[ $column ] );
        }
        
        return $rules;
    }
    
    /**
     * @inheritDoc
     */
    public function cast( array $data ): array {
        foreach ( $this->rules as $column => $rule ) {
            if ( ! isset( $data[ $column ] ) ) {
                continue;
            }
            
            $data[ $column ] = $this->transformer->getType( $rule )->transformToPhpValue( $data[ $column ] );
        }
        
        return $data;
    }
    
    public function uncast( array $data ): array {
        foreach ( $this->rules as $column => $rule ) {
            if ( ! isset( $data[ $column ] ) ) {
                continue;
            }
        
            $data[ $column ] = $this->transformer->getType( $rule )->transformToDatabaseValue( $data[ $column ] );
        }
    
        return $data;
    }
    
}