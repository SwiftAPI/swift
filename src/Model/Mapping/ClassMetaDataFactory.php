<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Mapping;

use Swift\Code\ReflectionFactory;
use Swift\Configuration\ConfigurationInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\TypeSystem\Enum;
use Swift\Model\EntityInterface;
use Swift\Model\Types\TypeTransformer;

/**
 * Class ClassMetaDataFactory
 * @package Swift\Model\Mapping
 */
#[Autowire]
class ClassMetaDataFactory {

    /**
     * ClassMetaDataFactory constructor.
     */
    public function __construct(
        private ClassMetaDataCacheBag $cache,
        private ReflectionFactory $reflectionFactory,
        private AttributeReader $attributeReader,
        private ConfigurationInterface $configuration,
        private NamingStrategyInterface $entityMappingNamingStrategy,
        private TypeTransformer $typeTransformer,
    ) {
    }

    public function getClassMetaData( string $entity ): ClassMetaData {
        if ( $this->cache->has( $entity ) ) {
            return $this->cache->get( $entity );
        }

        // Validate provided class
        if ( ! class_exists( $entity ) ) {
            throw new \InvalidArgumentException( sprintf( '%s is not found as a class', $entity ) );
        }
        if ( ! is_a( $entity, EntityInterface::class, true ) ) {
            throw new \InvalidArgumentException( sprintf( '%s expected, but got %s', EntityInterface::class, $entity ) );
        }

        $reflection     = $this->reflectionFactory->getReflectionClass( $entity );
        $tableAttribute = $this->attributeReader->getTableAttribute( $entity );
        $table          = new Table(
            $reflection->getName(),
            $tableAttribute->name,
            $this->configuration->get( 'connection.prefix', 'database' ),
        );

        foreach ( $reflection->getProperties() as $property ) {
            $fieldAttribute = $this->attributeReader->getFieldAttribute( $property );
            if ( ! $fieldAttribute ) {
                continue;
            }

            $index = null;
            if ( $fieldAttribute->isPrimaryKey() ) {
                $index = new IndexType( IndexType::PRIMARY );
            } elseif ( $fieldAttribute->isUnique() ) {
                $index = new IndexType( IndexType::UNIQUE );
            } elseif ( $fieldAttribute->isIndex() ) {
                $index = new IndexType( IndexType::INDEX );
            }

            $field = new Field(
                $property->getName(),
                $fieldAttribute->getName(),
                $fieldAttribute,
                $this->typeTransformer->getType( $fieldAttribute->getType() ),
                $property,
                $fieldAttribute->getSerialize(),
                ! empty( $fieldAttribute->getEnum() ) && is_a( Enum::class, $fieldAttribute->getEnum(), true ) ?
                    new ( $fieldAttribute->getEnum() )() : null,
                $index,
            );

            $table->addField( $field );
        }

        // Compile the table based on the fields
        foreach ($table->getFields() as $field) {
            if ($field->getIndex()) {
                $table->addIndex( new Index(
                    $this->entityMappingNamingStrategy->getIndexName($table, [$field], $field->getIndex()),
                    $field->getIndex(),
                    [$field],
                ) );
            }

            if ($field->getIndex()?->getValue() === IndexType::PRIMARY) {
                $table->setPrimaryKey( $field );
            }
        }

        $classMeta = new ClassMetaData( $table, $reflection );
        $this->cache->set( $entity, $classMeta );

        return $classMeta;
    }

}