<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes;

use Attribute;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Types\FieldTypes;
use InvalidArgumentException;

/**
 * Class Field
 * @package Swift\Orm\Attributes
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
#[DI( autowire: false )]
final class Field {
    
    public string|object $type;
    public string $name;
    
    /**
     * Field constructor.
     *
     * @param string|array  $name    name of the field (column)
     * @param bool          $primary whether this field is the primary key
     * @param string|object $type
     * @param array         $serialize
     * @param int           $length
     * @param bool          $empty   whether field is nullable (defaults to false)
     * @param bool          $unique  whether to add a unique constraint to the field
     * @param bool          $index
     * @param string|null   $enum    optional enum to validate
     * @param string|null   $comment database comment
     */
    public function __construct(
        string|array   $name,
        public bool    $primary = false,
        string|object  $type = FieldTypes::TEXT,
        public array   $serialize = [],
        public int     $length = 0,
        public bool    $empty = false,
        public bool    $unique = false,
        public bool    $index = false,
        public ?string $enum = null,
        public ?string $comment = null,
    ) {
        if ( is_array( $name ) ) {
            $name = $name[ 0 ] ?? '';
        }
        $this->name = $name;
        if ( is_a( $type, \StringBackedEnum::class ) ) {
            $this->type = $type->value;
        } else if ( is_a( $type, \UnitEnum::class ) ) {
            $this->type = $type->name;
        }
        
        if ( ! is_null( $this->enum ) && ! enum_exists( $this->enum ) ) {
            throw new InvalidArgumentException( sprintf( '%s should be a valid enum', $this->enum ) );
        }
    }
    
    public function toObject(): \stdClass {
        $object = new \stdClass();
        foreach ( get_object_vars( $this ) as $name => $var ) {
            $object->{$name} = $var;
        }
        
        return $object;
    }
    
    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * @return bool
     */
    public function isPrimaryKey(): bool {
        return $this->primary;
    }
    
    /**
     * @return string
     */
    public function getType(): string {
        return strtolower($this->type);
    }
    
    /**
     * @return array
     */
    public function getSerialize(): array {
        return $this->serialize;
    }
    
    /**
     * @return int
     */
    public function getLength(): int {
        return $this->length;
    }
    
    /**
     * @return bool
     */
    public function isNullable(): bool {
        return $this->empty;
    }
    
    /**
     * @return bool
     */
    public function isUnique(): bool {
        return $this->unique;
    }
    
    /**
     * @return bool
     */
    public function isIndex(): bool {
        return $this->index;
    }
    
    /**
     * @return string|null
     */
    public function getEnum(): ?string {
        return $this->enum;
    }
    
    /**
     * @return string|null
     */
    public function getComment(): ?string {
        return $this->comment;
    }
    
    
}