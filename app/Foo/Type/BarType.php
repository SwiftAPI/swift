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
 * Class BarType
 * @package Foo\Type
 */
#[Type]
class BarType {

    /**
     * FooType constructor.
     *
     * @param string $id
     * @param string $title
     */
    public function __construct(
        #[Field] public string $id,
        #[Field] public string $title,
    ) {
    }

    #[Field(name: 'author')]
    public function getAuthor(): AuthorType {
        return new AuthorType(id: '3', name: 'Foo Bar');
    }

    #[Field(name: 'reviews', type: ReviewType::class, isList: true)]
    public function getReviews(): array {
        return array(
            new ReviewType(id: '1', username: 'Foo', content: 'Lorem ipsum dolor'),
            new ReviewType(id: '2', username: 'Bar', content: 'Lorem ipsum dolor'),
            new ReviewType(id: '3', username: 'Fubar', content: 'Lorem ipsum dolor'),
        );
    }

}