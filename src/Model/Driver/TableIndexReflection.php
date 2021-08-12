<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Driver;

use Swift\Kernel\Attributes\DI;
use Swift\Model\Mapping\Index;
use Swift\Model\Mapping\IndexType;

/**
 * Class TableIndexReflection
 * @package Swift\Model\Driver
 */
#[DI( autowire: false )]
class TableIndexReflection {

    private IndexType $indexType;

    /**
     * @param string   $name
     * @param bool     $unique
     * @param bool     $primary
     * @param string[] $columns
     */
    public function __construct(
        private string $name,
        private bool   $unique,
        private bool   $primary,
        private array  $columns,
    ) {
        if ( $this->isPrimary() ) {
            $this->indexType = new IndexType( IndexType::PRIMARY );
        } else if ( $this->isUnique() ) {
            $this->indexType = new IndexType( IndexType::UNIQUE );
        } else {
            $this->indexType = new IndexType( IndexType::INDEX );
        }
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool {
        return $this->primary;
    }

    /**
     * @return bool
     */
    public function isUnique(): bool {
        return $this->unique;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getColumns(): array {
        return $this->columns;
    }

    /**
     * @return \Swift\Model\Mapping\IndexType
     */
    public function getIndexType(): IndexType {
        return $this->indexType;
    }

    public static function toIndex( TableIndexReflection $indexReflection ): Index {
        return new Index(
            $indexReflection->getName(),
            $indexReflection->getIndexType(),
            [],
        );
    }


}