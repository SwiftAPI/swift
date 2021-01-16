<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Generators;

use GraphQL\Type\Definition\Type;
use Swift\GraphQl\TypeRegistry;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\Attributes\DI;

#[DI(exclude: true)]
interface GeneratorInterface {

    /**
     * Adjust or generate type definition before assignment
     *
     * @param mixed $type
     * @param TypeRegistry $typeRegistry
     *
     * @return mixed
     */
    public function generate( ObjectType $type, TypeRegistry $typeRegistry ): Type;

}