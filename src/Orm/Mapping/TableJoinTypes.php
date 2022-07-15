<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping;


use Swift\DependencyInjection\Attributes\DI;

/**
 * Class TableJoinTypesEnum
 * @package Swift\Orm\Types
 */
#[DI(autowire: false)]
enum TableJoinTypes: string {

    case INNER = 'INNER';
    case OUTER = 'OUTER';
    case LEFT = 'LEFT';
    case RIGHT = 'RIGHT';

}