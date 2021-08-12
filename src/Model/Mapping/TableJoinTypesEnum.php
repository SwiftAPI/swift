<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Mapping;


use Swift\Kernel\Attributes\DI;
use Swift\Kernel\TypeSystem\Enum;

/**
 * Class TableJoinTypesEnum
 * @package Swift\Model\Types
 */
#[DI(autowire: false)]
class TableJoinTypesEnum extends Enum {

    public const INNER = 'INNER';
    public const OUTER = 'OUTER';
    public const LEFT = 'LEFT';
    public const RIGHT = 'RIGHT';

}