<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Definition;

use Swift\Orm\Types\TypeInterface;

/**
 * Class Field
 * @package Swift\Orm\Mapping\Definition
 */
class Field {
    
    /**
     * Field constructor.
     */
    public function __construct(
        private string                      $propertyName,
        private string                      $databaseName,
        private \Swift\Orm\Attributes\Field $fieldAttribute,
        private TypeInterface               $type,
        private ?\ReflectionProperty        $reflectionProperty = null,
        private array                       $serializations = [],
        private ?string                     $enum = null,
        private ?IndexType                  $index = null,
        private bool                        $hidden = false,
    ) {
    }
    
    /**
     * @return string
     */
    public function getPropertyName(): string {
        return $this->propertyName;
    }
    
    /**
     * @return string
     */
    public function getDatabaseName(): string {
        return $this->databaseName;
    }
    
    /**
     * @return \ReflectionProperty
     */
    public function getReflectionProperty(): \ReflectionProperty {
        return $this->reflectionProperty;
    }
    
    /**
     * @return array
     */
    public function getSerializations(): array {
        return $this->serializations;
    }
    
    /**
     * @return string|null
     */
    public function getEnum(): ?string {
        return $this->enum;
    }
    
    /**
     * @return IndexType|null
     */
    public function getIndex(): ?IndexType {
        return $this->index;
    }
    
    public function isNullable(): bool {
        return $this->fieldAttribute->isNullable();
    }
    
    public function getLength(): ?int {
        return ( $this->fieldAttribute->getLength() && is_numeric( $this->fieldAttribute->getLength() ) && ( $this->fieldAttribute->getLength() > 0 ) ) ?
            $this->fieldAttribute->getLength() : null;
    }
    
    public function getComment(): string {
        return sprintf(
            '"SWIFT_TYPE=%s%s"',
            str_replace( '\\', '/', $this->getType()::class ),
            $this->getFieldAttribute()->getComment() ? ', ' . $this->getFieldAttribute()->getComment() : '',
        );
    }
    
    /**
     * @return TypeInterface
     */
    public function getType(): TypeInterface {
        return $this->type;
    }
    
    /**
     * @return bool
     */
    public function isHidden(): bool {
        return $this->hidden;
    }
    
    /**
     * @return \Swift\Orm\Attributes\Field
     */
    public function getFieldAttribute(): \Swift\Orm\Attributes\Field {
        return $this->fieldAttribute;
    }
    
    public function __serialize(): array {
        $serialized                         = get_object_vars( $this );
        $serialized[ 'reflectionProperty' ] = null;
        
        return $serialized;
    }
    
    
}