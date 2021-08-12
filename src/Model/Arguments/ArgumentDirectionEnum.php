<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Arguments;

use Swift\Kernel\Attributes\DI;
use Swift\Kernel\TypeSystem\Enum;

/**
 * Class ArgumentDirectionEnum
 * @package Swift\Model\Types
 */
#[DI(exclude: true)]
class ArgumentDirectionEnum extends Enum {

    public const ASC = 'ASC';
    public const DESC = 'DESC';

}