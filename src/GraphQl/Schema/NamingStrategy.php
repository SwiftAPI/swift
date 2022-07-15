<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema;


use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;

class NamingStrategy {
    
    protected readonly Inflector $inflector;
    
    public function __construct() {
        $this->inflector = InflectorFactory::create()->build();
    }
    
    public function singleQueryName( string $table ): string {
        return ucfirst( $this->inflector->singularize( $this->inflector->camelize( $table ) ) );
    }
    
    public function connectionName( string $table ): string {
        return ucfirst( $this->inflector->singularize( $this->inflector->camelize( $table ) ) ) . 'Connection';
    }
    
    public function edgeName( string $table ): string {
        return ucfirst( $this->inflector->singularize( $this->inflector->camelize( $table ) ) ) . 'Edge';
    }
    
    public function listQueryName( string $table ): string {
        return ucfirst( $this->inflector->pluralize( $this->inflector->camelize( $table ) ) );
    }
    
    public function listQueryWhereArgs( string $table ): string {
        return $this->listQueryName( $table ) . 'WhereArgs';
    }
    
    public function inputWhereArgsFieldName( string $propertyName, string $prefix ): string {
        return ucfirst( $this->inflector->camelize( $propertyName ) . $prefix . 'Input' );
    }
    
    public function getMutationName( string $table ): string {
        return ucfirst( $this->inflector->singularize( $this->inflector->camelize( $table ) ) );
    }
    
    public function getMutationUpdateName( string $table ): string {
        return $this->getMutationName( $table ) . 'Update';
    }
    
    public function getMutationCreateName( string $table ): string {
        return $this->getMutationName( $table ) . 'Create';
    }
    
    public function getMutationUpdateInputName( string $table ): string {
        return $this->getMutationName( $table ) . 'UpdateInput';
    }
    
    public function getMutationCreateInputName( string $table ): string {
        return $this->getMutationName( $table ) . 'CreateInput';
    }
    
}