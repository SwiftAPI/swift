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
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\Attributes\Table;
use Swift\Model\Exceptions\InvalidConfigurationException;

/**
 * Class AttributeReader
 * @package Swift\Model\Mapping
 */
#[Autowire]
class AttributeReader {

    /**
     * AttributeReader constructor.
     */
    public function __construct(
        private ReflectionFactory $reflectionFactory,
    ) {
    }

    public function getTableAttribute( string $className ): Table {
        $reflection       = $this->reflectionFactory->getReflectionClass( $className );
        $dbTableAttribute = $this->reflectionFactory->getAttributeReader()->getClassAnnotation( $reflection, Table::class );

        if ( ! $dbTableAttribute ) {
            throw new InvalidConfigurationException( sprintf( 'Entity %s missing Table attribute, this is an invalid use case. Please add %s attribute to class', static::class, Table::class ) );
        }

        return $dbTableAttribute;
    }

    /**
     * @param \ReflectionProperty $property
     *
     * @return \Swift\Model\Attributes\Field|null
     */
    public function getFieldAttribute( \ReflectionProperty $property ): ?\Swift\Model\Attributes\Field {
        $attributes = $property->getAttributes( \Swift\Model\Attributes\Field::class );

        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]?->newInstance();
    }

}