<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\Type;
use Swift\GraphQl\Exceptions\InvalidTypeException;
use Swift\GraphQl\Types\ObjectType;
use GraphQL\Type\Definition\ObjectType as GraphQlObjectType;
use Swift\Kernel\Attributes\DI;

#[DI(tags: ['graphql.type_registry'])]
interface TypeRegistryInterface {

    /**
     * Add type declaration
     *
     * @param ObjectType $type
     *
     * @throws InvalidTypeException
     */
    public function addType( ObjectType $type ): void;

    /**
     * @param ObjectType[] $types
     *
     * @throws InvalidTypeException
     */
    public function addTypes( array $types ): void;

    /**
     * @param string $name
     *
     * @return ObjectType|Type|null
     */
    public function getTypeByClass( string $name ): ObjectType|Type|null;

    /**
     * @return array
     */
    public function getTypes(): array;

    /**
     * Add object type which extends another. Compile later when all types have been registered.
     *
     * @param ObjectType $type
     */
    public function addExtension( ObjectType $type ): void;

    /**
     * Compile added types 
     */
    public function compile(): void;

}