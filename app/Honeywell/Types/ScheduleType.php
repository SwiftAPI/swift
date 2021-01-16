<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Honeywell\Types;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\TypeSystem\Defaults\Datetime\WeekdaysEnum;

#[DI(autowire: false), Type]
class ScheduleType {

    /**
     * ScheduleType constructor.
     *
     * @param int|null $id
     * @param string $day
     * @param int $deviceID
     * @param string $startTime
     * @param float $temp
     * @param string $created
     * @param string $modified
     */
    public function __construct(
        #[Field] public ?int $id,
        #[Field(type: WeekdaysEnum::class)] public string $day,
        #[Field] public int $deviceID,
        #[Field] public string $startTime,
        #[Field] public float $temp,
        #[Field] public string $created,
        #[Field] public string $modified,
    ) {
    }
}