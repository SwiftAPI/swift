<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Mapping;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Swift\Code\ReflectionClass;
use Swift\Kernel\Attributes\DI;
use Swift\Model\Attributes\Field;
use Swift\Model\Entity\EntityManager;

/**
 * Class ClassMetaDataDeprecated
 * @package Swift\Model\Mapping
 */
#[DI(autowire: false)]
class ClassMetaDataDeprecated {

    /**
     * ClassMetaData constructor.
     */
    public function __construct(
        private ReflectionClass $reflectionClass,
        private ?string $primaryKey,
        protected array $propertyMap = [],
        protected array $propertyActions = [],
        protected array $propertyProps = [],
        protected ?string $tableName = null,
        protected array $indexes = [],
        protected array $joins = [],
        protected string $entityName = '',
    ) {
    }

    /**
     * @return ReflectionClass
     */
    public function getReflectionClass(): ReflectionClass {
        return $this->reflectionClass;
    }

    /**
     * @return string|null
     */
    public function getPrimaryKey(): ?string {
        return $this->primaryKey;
    }

    /**
     * @return array
     */
    public function getPropertyMap(): array {
        return $this->propertyMap;
    }

    /**
     * @return array
     */
    public function getPropertyActions(): array {
        return $this->propertyActions;
    }

    /**
     * @return Field[]
     */
    public function getPropertyProps(): array {
        return $this->propertyProps;
    }

    /**
     * Method to get table name
     *
     * @return string
     */
    public function getTableName(): string {
        return $this->tableName;
    }

    /**
     * @param string $prefix
     *
     * @return string|null
     */
    public function getTableNamePrefixed(string $prefix): ?string {
        return $prefix . $this->tableName;
    }

    /**
     * @return array
     */
    public function getIndexes(): array {
        return $this->indexes;
    }

    /**
     * @return array
     */
    public function getJoins(): array {
        return $this->joins;
    }


    /**
     * @return string
     */
    public function getEntityName(): string {
        return $this->entityName;
    }

    /**
     * Method to validate if a fieldName(property) is available
     *
     * @param string $fieldName
     *
     * @return bool
     */
    #[Pure]
    public function hasField( string $fieldName ): bool {
        return array_key_exists( $fieldName, $this->propertyMap );
    }

    /**
     * Method to get property's db name
     *
     * @param string $property
     * @param bool $prefixWithEntityName
     *
     * @return string
     */
    public function getPropertyDBName( string $property, bool $prefixWithEntityName = false ): string {
        if ( ! $this->hasField( $property ) ) {
            throw new InvalidArgumentException( 'Property ' . $property . ' does not exist for ' . get_class( $this ), 500 );
        }

        return $prefixWithEntityName ? $this->entityName . '.' . $this->propertyMap[ $property ] : $this->propertyMap[ $property ];
    }

    /**
     * Method to get property name by db name
     *
     * @param string $dbName
     *
     * @return string|null
     */
    #[Pure]
    public function getPropertyNameByDbName( string $dbName ): ?string {
        $property = array_search( $dbName, $this->propertyMap, true );

        return ( $property && property_exists( $this, $property ) ) ? $property : null;
    }

}