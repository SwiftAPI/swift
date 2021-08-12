<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Mapping;

use JetBrains\PhpStorm\ArrayShape;
use Swift\Code\ReflectionClass;
use Swift\Code\ReflectionFactory;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\ServiceLocatorInterface;
use Swift\Model\Attributes\Field;
use Swift\Model\Attributes\Join;
use Swift\Model\Entity;
use Swift\Model\Exceptions\InvalidConfigurationException;
use Swift\Model\Types\FieldTypes;

/**
 * Class ClassMetaDataFactoryDeprecated
 * @package Swift\Model\Mapping
 */
#[Autowire]
class ClassMetaDataFactoryDeprecated {

    private array $metaDataCache = [];

    /**
     * ClassMetaDataFactory constructor.
     */
    public function __construct(
        private ReflectionFactory       $reflectionFactory,
        private AttributeReader         $attributeReader,
        private ServiceLocatorInterface $serviceLocator,
    ) {
    }

    public function getMetaData( string $className ): ClassMetaDataDeprecated {
        // Return cached Meta Data if found
        if ( array_key_exists( $className, $this->metaDataCache ) ) {
            return $this->metaDataCache[ $className ];
        }

        // Validate provided class
        if ( ! class_exists( $className ) ) {
            throw new \InvalidArgumentException( sprintf( '%s is not found as a class', $className ) );
        }
        if ( ! is_a( $className, Entity::class, true ) ) {
            throw new \InvalidArgumentException( sprintf( '%s expected, but got %s', Entity::class, $className ) );
        }

        $reflection        = $this->reflectionFactory->getReflectionClass( $className );
        $tableAttribute    = $this->attributeReader->getTableAttribute( $className );
        $propertiesMapping = $this->mapProperties( $reflection );

        $entityName = strtolower( str_replace( '\\', '_', $reflection->getName() ) );
        $tableName  = $tableAttribute->name;

        $classMetaData                     = new ClassMetaDataDeprecated(
            $reflection,
            $propertiesMapping['primaryKey'],
            $propertiesMapping['propertyMap'],
            $propertiesMapping['propertyActions'],
            $propertiesMapping['propertyProps'],
            $tableName,
            $propertiesMapping['indexes'],
            $propertiesMapping['joins'],
            $entityName,
        );
        $this->metaDataCache[ $className ] = $classMetaData;

        return $this->metaDataCache[ $className ];
    }

    /**
     * Method to map object properties to table columns
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return array
     */
    #[ArrayShape( [ 'propertyActions' => "array", 'joins' => "array", 'propertyMap' => "array", 'propertyProps' => "array", 'indexes' => "array", 'primaryKey' => "null|string" ] )]
    protected function mapProperties( ReflectionClass $reflectionClass ): array {
        $propertyActions = [];
        $joins           = [];
        $propertyMap     = [];
        $propertyProps   = [];
        $indexes         = [];
        $primaryKey      = null;

        $propertyActions['serialize'] = [];
        $properties                   = $reflectionClass->getProperties();
        foreach ( $properties as $property ) {
            if ( ! empty( $property->getAttributes( Join::class ) ) ) {
                $join                          = ( $property->getAttributes( Join::class )[0]->newInstance() )->toObject();
                $join->instance                = $this->serviceLocator->get( $join->entity );
                $joins[ $property->getName() ] = $join;
            }

            /** @var Field|null $attribute */
            $attribute = ! empty( $property->getAttributes( name: Field::class ) ) ? ( $property->getAttributes( name: Field::class )[0]->newInstance() )->toObject() : null;

            $attribute = (object) $attribute;
            if ( isset( $property->name, $attribute->name ) ) {
                $propertyMap[ $property->name ] = $attribute->name;

                $propertyProps[ $property->name ] = $attribute;

                if ( $attribute->index ) {
                    $indexes[] = $attribute->name;
                }

                // Add default serializations
                if ( ( $attribute->type === FieldTypes::TIMESTAMP ) && ! in_array( FieldTypes::TIMESTAMP, $attribute->serialize, true ) ) {
                    if ( ! isset( $propertyActions['serialize'][ $property->name ] ) ) {
                        $propertyActions['serialize'][ $property->name ] = [];
                    }
                    $propertyActions['serialize'][ $property->name ][] = FieldTypes::TIMESTAMP;
                }
                if ( ( $attribute->type === FieldTypes::DATETIME ) && ! in_array( FieldTypes::DATETIME, $attribute->serialize, true ) ) {
                    if ( ! isset( $propertyActions['serialize'][ $property->name ] ) ) {
                        $propertyActions['serialize'][ $property->name ] = [];
                    }
                    $propertyActions['serialize'][ $property->name ][] = FieldTypes::DATETIME;
                }
            }

            if ( isset( $property->name, $attribute->serialize ) && $attribute && ! empty( $attribute->serialize ) ) {
                // Set serialize actions
                $propertyActions['serialize'][ $property->name ] = $attribute->serialize;
            }

            // Check for primary key
            if ( isset( $property->name, $attribute->primary ) && $attribute ) {
                if ( $attribute->primary && isset( $this->primaryKey ) ) {
                    throw new InvalidConfigurationException( 'Multiple primary keys found' );
                }

                if ( $attribute->primary ) {
                    $primaryKey = $property->name;
                }
            }
        }

        return array(
            'propertyActions' => $propertyActions,
            'joins'           => $joins,
            'propertyMap'     => $propertyMap,
            'propertyProps'   => $propertyProps,
            'indexes'         => $indexes,
            'primaryKey'      => $primaryKey,
        );
    }

}