<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Mapping;

use Swift\Kernel\TypeSystem\Enum;
use Swift\Model\Types\TypeInterface;

/**
 * Class Field
 * @package Swift\Model\Mapping
 */
class Field {

    /**
     * Field constructor.
     */
    public function __construct(
        private string                        $propertyName,
        private string                        $databaseName,
        private \Swift\Model\Attributes\Field $fieldAttribute,
        private TypeInterface                 $type,
        private ?\ReflectionProperty          $reflectionProperty = null,
        private array                         $serializations = [],
        private ?Enum                         $enum = null,
        private ?IndexType                    $index = null,
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
     * @return Enum|null
     */
    public function getEnum(): ?Enum {
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
     * @return \Swift\Model\Attributes\Field
     */
    public function getFieldAttribute(): \Swift\Model\Attributes\Field {
        return $this->fieldAttribute;
    }


}