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

/**
 * Class ThermostatState
 * @package Honeywell\Type
 */
#[DI(autowire: false), Type]
class ThermostatState {

    /**
     * ThermostatState constructor.
     *
     * @param int $id
     * @param bool $heating
     * @param float $indoorTemp
     * @param bool $occupated
     * @param float $outdoorTemp
     * @param bool $serviceUp
     * @param float $setTemp
     */
    public function __construct(
        #[Field] public int $id,
        #[Field] public bool $heating,
        #[Field] public float $indoorTemp,
        #[Field] public bool $occupated,
        #[Field] public float $outdoorTemp,
        #[Field] public bool $serviceUp,
        #[Field] public float $setTemp,
    ) {
    }
}