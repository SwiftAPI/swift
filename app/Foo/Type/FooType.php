<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Foo\Type;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\InputType;
use Swift\GraphQl\Types\Type;

/**
 * Class FooType
 * @package Foo\Type
 */
#[InputType]
class FooType {

    /**
     * FooType constructor.
     *
     * @param string $id
     */
    public function __construct(
        #[Field(type: Type::ID)] public string $id,
    ) {
    }

}