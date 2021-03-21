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
 * Class ReviewType
 * @package Foo\Type
 */
#[Type]
class ReviewType {

    /**
     * ReviewType constructor.
     *
     * @param string $id
     * @param string $username
     * @param string $content
     */
    public function __construct(
        #[Field] public string $id,
        #[Field] public string $username,
        #[Field] public string $content,
    ) {
    }

}