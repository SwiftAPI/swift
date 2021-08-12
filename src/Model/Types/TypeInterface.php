<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Types;

use Swift\Kernel\Attributes\DI;
use Swift\Kernel\KernelDiTags;
use Swift\Model\Mapping\Field;
use Swift\Model\Query\TableQuery;

/**
 * Interface TypeInterface
 * @package Swift\Model\Types
 */
#[DI(tags: [KernelDiTags::ENTITY_TYPE])]
interface TypeInterface {

    /**
     * Return sql used to create column in the data database
     *
     * @param \Swift\Model\Mapping\Field    $field
     * @param \Swift\Model\Query\TableQuery $query
     *
     * @return string
     */
    public function getSqlDeclaration( Field $field, TableQuery $query ): string;

    public function transformToPhpValue( mixed $value ): mixed;

    public function transformToDatabaseValue( mixed $value ): mixed;

    public function getName(): string;

}