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
use Swift\GraphQl\Attributes\Type;

/**
 * Class AuthorType
 * @package Foo\Type
 */
#[Type]
class AuthorType {

    /**
     * FooType constructor.
     *
     * @param string $id
     * @param string $name
     */
    public function __construct(
        #[Field] public string $id,
        #[Field] public string $name,
    ) {
    }

}