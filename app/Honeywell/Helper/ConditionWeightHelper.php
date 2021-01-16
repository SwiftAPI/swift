<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Honeywell\Helper;

use Honeywell\Types\ConditionTypeEnum;
use JetBrains\PhpStorm\Pure;

/**
 * Class ConditionWeightHelper
 * @package Honeywell\Helper
 */
class ConditionWeightHelper {

    /**
     * @param ConditionTypeEnum $type
     *
     * @return int
     */
    #[Pure] public static function weightByType( ConditionTypeEnum $type ): int {
        return match ( $type->getValue() ) {
            ConditionTypeEnum::DATE => 5,
            ConditionTypeEnum::OUTSIDE_TEMP => 3,
            ConditionTypeEnum::WEEKDAY, ConditionTypeEnum::TIME => 2,
            default => 1,
        };
    }

}