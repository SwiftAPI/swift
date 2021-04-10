<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Types;

use Swift\Kernel\Attributes\DI;
use Swift\Kernel\TypeSystem\Enum;

/**
 * Class ArgumentComparisonTypesEnum
 * @package Swift\Model\Types
 */
#[DI(autowire: false)]
class ArgumentComparisonTypesEnum extends Enum {

    public const GREATER_THAN = '>';
    public const LESS_THAN = '<';
    public const EQUALS = '=';

}