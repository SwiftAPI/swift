<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Foo\Repository;

use Swift\Kernel\Attributes\DI;
use Swift\Model\Attributes\DB;
use Swift\Model\Entity;
use Swift\Model\EntityInterface;

/**
 * Class FooRepository
 * @package Foo\Repository
 */
#[DI(aliases: [EntityInterface::class . ' $fooRepository']), DB(table: 'foo_bar')]
class FooRepository extends Entity {

}