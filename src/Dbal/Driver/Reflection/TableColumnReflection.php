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
use Swift\Orm\Mapping\Definition\Field;
use Swift\Orm\Types\TypeInterface;


#[DI( autowire: false )]
#[Deprecated]
class TableColumnReflection {

    /**
     * @param string   $name
     * @param string   $table
     * @param string   $nativetype
     * @param int|null $size
     * @param bool     $nullable
     * @param mixed    $default
     * @param bool     $autoincrement
     * @param array    $vendor
     */
    public function __construct(
        private string $name,
        private string $table,
        private string $nativetype,
        private ?int   $size,
        private bool   $nullable,
        private mixed  $default,
        private bool   $autoincrement,
        private array  $vendor,
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTable(): string {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getNativeType(): string {
        return strtolower($this->nativetype);
    }

    /**
     * @return int
     */
    public function getSize(): int {
        return $this->size;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool {
        return $this->nullable;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function isAutoincrement(): bool {
        return $this->autoincrement;
    }

    /**
     * @return array
     */
    public function getVendor(): array {
        return $this->vendor;
    }

    public static function toField( TableColumnReflection $column, string $className, TypeInterface $type ): Field {
        return new Field(
            $column->getName(),
            $column->getName(),
            new \Swift\Orm\Attributes\Field($column->getName()),
            $type,
            null,
            [],
            null,
            null,
        );
    }

}