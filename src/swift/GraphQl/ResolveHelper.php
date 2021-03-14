<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl;

use GraphQL\Exception\InvalidArgument;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class ResolveHelper
 * @package Swift\GraphQl
 */
#[Autowire]
class ResolveHelper {

    public function getArgumentType( string|null $parameterType, \ReflectionNamedType|\ReflectionUnionType|null $reflectionType ): string|array {
        $type = $parameterType ?? $reflectionType;

        if (is_null($type)) {
            throw new InvalidArgument('No type defined');
        }
        if (is_string($type)) {
            return $type;
        }
        if ($type instanceof \ReflectionNamedType) {
            return $type->getName();
        }

        $types = array();
        foreach ($reflectionType->getTypes() as $type) {
            $types[] = $type->getName();
        }

        return $types;
    }

    public function getReturnType( string|null $parameterType, \ReflectionNamedType|\ReflectionUnionType|null $reflectionType ): string|array {
        $type = $parameterType ?? $reflectionType;

        if (is_null($type)) {
            throw new InvalidArgument('No type defined');
        }
        if (is_string($type)) {
            return $type;
        }
        if ($type instanceof \ReflectionNamedType) {
            return $type->getName();
        }

        $types = array();
        foreach ($reflectionType->getTypes() as $type) {
            $types[] = $type->getName();
        }

        return $types;
    }

}