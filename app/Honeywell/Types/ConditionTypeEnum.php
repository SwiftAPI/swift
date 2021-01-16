<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Honeywell\Types;


use Swift\Kernel\TypeSystem\Enum;

/**
 * Class ConditionTypeEnum
 * @package Honeywell\Types
 */
class ConditionTypeEnum extends Enum {

    public const WEEKDAY = 'WEEKDAY';
    public const TIME = 'TIME';
    public CONST OUTSIDE_TEMP = 'OUTSIDE_TEMP';
    public const DATE = 'DATE';

}