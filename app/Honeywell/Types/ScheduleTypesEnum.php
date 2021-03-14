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
 * Class ScheduleTypesEnum
 * @package Honeywell\Types
 */
class ScheduleTypesEnum extends Enum {

    public const DEFAULT = 'DEFAULT';
    public const RECURRING = 'RECURRING';
    public const ONCE = 'ONCE';
    public const TILL_NEXT = 'TILL_NEXT';

}