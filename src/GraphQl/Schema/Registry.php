<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema;


use GraphQL\Type\Definition\Directive;
use Swift\GraphQl\Exceptions\DuplicateTypeException;
use Swift\GraphQl\Schema\Builder\ConnectionBuilder;
use Swift\GraphQl\Schema\Builder\DirectiveBuilder;
use Swift\GraphQl\Schema\Builder\EnumBuilder;
use Swift\GraphQl\Schema\Builder\InputObjectBuilder;
use Swift\GraphQl\Schema\Builder\InterfaceBuilder;
use Swift\GraphQl\Schema\Builder\ObjectBuilder;
use Swift\GraphQl\Schema\Middleware\SchemaMiddlewareExecutor;

class Registry {
    
    protected array $types = [];
    public static array $typeMap = [];
    public static array $alias = [];
    protected array $directives = [];
    public static array $directivesMap = [];
    
    public function __construct(
        protected readonly SchemaMiddlewareExecutor $middlewareExecutor,
    ) {
    }
    
    
    public function objectType( ObjectBuilder|ConnectionBuilder $type ): void {
        if ( array_key_exists( $type->getName(), $this->types ) ) {
            throw new DuplicateTypeException( "Type {$type->getName()} already exists" );
        }
        
        $type = $this->runMiddleware( $type );
        
        if ( ! $type ) {
            return;
        }
        
        $this->types[ $type->getName() ] = $type;
    }
    
    public function directive( DirectiveBuilder $type ): void {
        if ( array_key_exists( $type->getName(), $this->directives ) ) {
            throw new DuplicateTypeException( "Type {$type->getName()} already exists" );
        }
        
        $this->directives[ $type->getName() ] = $type;
    }
    
    public function inputObjectType( InputObjectBuilder $type ): void {
        if ( array_key_exists( $type->getName(), $this->types ) ) {
            throw new DuplicateTypeException( "Type {$type->getName()} already exists" );
        }
    
        $type = $this->runMiddleware( $type );
    
        if ( ! $type ) {
            return;
        }
        
        $this->types[ $type->getName() ] = $type;
    }
    
    public function enumType( EnumBuilder $type ): void {
        if ( array_key_exists( $type->getName(), $this->types ) ) {
            throw new DuplicateTypeException( "Type {$type->getName()} already exists" );
        }
    
        $type = $this->runMiddleware( $type );
    
        if ( ! $type ) {
            return;
        }
        
        $this->types[ $type->getName() ] = $type;
    }
    
    public function interfaceType( InterfaceBuilder $type ): void {
        if ( array_key_exists( $type->getName(), $this->types ) ) {
            throw new DuplicateTypeException( "Type {$type->getName()} already exists" );
        }
    
        $type = $this->runMiddleware( $type );
    
        if ( ! $type ) {
            return;
        }
        
        $this->types[ $type->getName() ] = $type;
    }
    
    public function extendType( string $type, callable $func ): void {
        if ( ! isset( $this->types[ $type ] ) ) {
            throw new DuplicateTypeException( "Type {$type} does not exist" );
        }
    
        $extend = $this->runMiddleware( $func( $this->types[ $type ], $this ) );
    
        if ( ! $extend ) {
            return;
        }
        
        $this->types[ $type ] = $extend;
    }
    
    public function getType( string $name ): mixed {
        return $this->types[ $name ] ?? null;
    }
    
    public function getAll(): array {
        return $this->types;
    }
    
    public function getDirectives(): array {
        return $this->directives;
    }
    
    public function build(): void {
        foreach ( $this->types as $key => $type ) {
            $this->types[ $key ]   = $type->buildType();
            self::$typeMap[ $key ] = $this->types[ $key ];
        }
        foreach ( $this->directives as $key => $type ) {
            $this->directives[ $key ]   = $type->buildType();
            self::$directivesMap[ $key ] = $this->directives[ $key ];
        }
        $this->directives = array_merge( $this->directives, Directive::getInternalDirectives() );
    }
    
    protected function runMiddleware( mixed $type ): mixed {
        return $this->middlewareExecutor->process( $type, $this, static function ( $type ) {
            return $type;
        } );
    }
    
}