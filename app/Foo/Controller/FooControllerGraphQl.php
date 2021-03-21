<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Foo\Controller;

use Foo\Type\BarInput;
use Foo\Type\BarType;
use Foo\Type\FooType;
use Swift\Controller\AbstractController;
use Swift\GraphQl\Attributes\Mutation;
use Swift\GraphQl\Attributes\Query;

/**
 * Class FooControllerGraphQl
 * @package Foo\Controller
 */
class FooControllerGraphQl extends AbstractController {

    /**
     * @param FooType $foo
     *
     * @return BarType
     */
    #[Query]
    public function foo( FooType $foo ): BarType {
        // Fetch some data here

        return new BarType(id: $foo->id, title: 'GraphQl result title');
    }

    /**
     * @param BarInput $bar
     *
     * @return BarType
     */
    #[Mutation]
    public function createBar( BarInput $bar ): BarType {
        // Create new entity based on input and return it's values

        return new BarType(id: '4', title: $bar->title);
    }

}