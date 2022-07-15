<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Dbal;


use Closure;
use stdClass;
use Swift\Orm\Mapping\ClassMetaData;

trait ResultTrait {
    
    public ?Closure $__classMetaDataReference = null;
    public bool $__serializeAsObject = false;
    
    public function initialize(
        ?Closure $__classMetaDataReference = null,
    ): static {
        $this->__classMetaDataReference = $__classMetaDataReference;
        
        $this->initializeEnums();
        
        return $this;
    }
    
    public function initializeEnums(): void {
        foreach ( $this->getClassMetaData()?->getEntity()->getFields() ?? [] as $field ) {
            if ( $field->getEnum() && $field->getPropertyName() && is_string( $this?->{$field->getPropertyName()} ) ) {
                $this->{$field->getPropertyName()} = $field->getEnum()::from( $this->{$field->getPropertyName()} );
            }
        }
    }
    
    /**
     * Serialize to object
     *
     * @return stdClass
     */
    public function toObject(): stdClass {
        $this->__serializeAsObject = true;
        
        return (object) $this->__serialize();
    }
    
    public function __serialize(): array {
        $values = [];
        
        if ( $classMetaData = $this->getClassMetaData() ) {
            foreach ( $classMetaData->getEntity()->getFields() as $field ) {
                if ( $field->getPropertyName() && isset( $this->{$field->getPropertyName()} ) ) {
                    $values[ $field->getPropertyName() ] = $this->{$field->getPropertyName()} ?? null;
                }
            }
            
            return $values;
        }
        
        foreach ( get_object_vars( $this ) as $key => $value ) {
            if ( in_array( $key, [ '__classMetaDataReference', '__serializeAsObject', '__cycle_orm_relation_props' ], true ) ) {
                continue;
            }
            $values[ $key ] = $value;
        }
        
        return $values;
    }
    
    /**
     * Serialize to array
     *
     * @return array
     */
    public function toArray(): array {
        $this->__serializeAsObject = false;
        
        return $this->__serialize();
    }
    
    /**
     * @return mixed
     */
    public function getPrimaryKeyValue(): mixed {
        return $this->{$this->getClassMetaData()?->getEntity()->getPrimaryKey()->getPropertyName()} ?? null;
    }
    
    public function __call( string $name, array $arguments ): mixed {
        if ( str_starts_with( $name, 'set' ) ) {
            $name         = substr( $name, 3 );
            $propertyName = lcfirst( $name );
            
            $this->{$propertyName} = $arguments[ 0 ];
            
            return $this;
        }
        
        return $this->__get( $name );
    }
    
    /**
     * @param \Closure|null $__classMetaDataReference
     */
    public function setClassMetaDataReference( ?Closure $__classMetaDataReference ): void {
        $this->__classMetaDataReference = $__classMetaDataReference;
    }
    
    /**
     * @return \Swift\Orm\Mapping\ClassMetaData|null
     */
    private function getClassMetaData(): ?ClassMetaData {
        $ref = $this?->__classMetaDataReference;
        
        return $ref ? $ref() : null;
    }
    
    public function __debugInfo(): ?array {
        $debugInfo = (array) $this;
        
        unset(
            $debugInfo[ '__classMetaDataReference' ],
            $debugInfo[ '__serializeAsObject' ],
        );
        
        return $debugInfo;
    }
    
    public function _getName(): string {
        if ( isset( $this->__cycle_orm_relation_props ) ) {
            return array_key_first( $this?->__cycle_orm_relation_props->getProperties() ) ?? static::class;
        }
        
        return static::class;
    }
    
}