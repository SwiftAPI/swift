<?php declare(strict_types=1);


namespace Swift\GraphQl;

use GraphQL\Type\Definition\Type;
use Swift\GraphQl\Exceptions\InvalidTypeException;
use Swift\GraphQl\Types\ObjectType;
use GraphQL\Type\Definition\ObjectType as GraphQlObjectType;

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
     * @param ObjectType $type
     */
    public function addQuery( ObjectType $type ): void;

    /**
     * @param string $name
     *
     * @return ObjectType|null
     */
    public function getQueryByName( string $name ): ObjectType|null;

    /**
     * @param ObjectType $type
     *
     * @return void
     */
    public function addMutation( ObjectType $type ): void;

    /**
     * @param string $name
     *
     * @return ObjectType|null
     */
    public function getMutationByName( string $name ): ObjectType|null;

    /**
     * @return GraphQlObjectType
     */
    public function getRootQuery(): GraphQlObjectType;

    /**
     * @return GraphQlObjectType
     */
    public function getRootMutation(): GraphQlObjectType;

    /**
     * Compile added types 
     */
    public function compile(): void;

}