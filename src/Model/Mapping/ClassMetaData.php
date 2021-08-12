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
 * Class ClassMetaData
 * @package Swift\Model\Mapping
 */
#[DI(autowire: false)]
class ClassMetaData {

    /**
     * ClassMetaData constructor.
     */
    public function __construct(
        private Table $table,
        private ReflectionClass $reflectionClass,
    ) {
    }

    /**
     * @return Table
     */
    public function getTable(): Table {
        return $this->table;
    }

    /**
     * @return ReflectionClass
     */
    public function getReflectionClass(): ReflectionClass {
        return $this->reflectionClass;
    }



}