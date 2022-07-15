<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal\Driver\Reflection;

use JetBrains\PhpStorm\Deprecated;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Mapping\Definition\Index;
use Swift\Orm\Mapping\Definition\IndexType;

/**
 * Class TableIndexReflection
 * @package Swift\Orm\Driver
 */
#[DI( autowire: false )]
#[Deprecated]
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
            $this->indexType = IndexType::PRIMARY;
        } else if ( $this->isUnique() ) {
            $this->indexType = IndexType::UNIQUE;
        } else {
            $this->indexType = IndexType::INDEX;
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
     * @return \Swift\Orm\Mapping\Definition\IndexType
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