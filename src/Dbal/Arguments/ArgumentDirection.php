<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal\Arguments;

use Swift\DependencyInjection\Attributes\DI;

/**
 * Class ArgumentDirectionEnum
 * @package Swift\Orm\Types
 */
#[DI(exclude: true)]
enum ArgumentDirection: string {

    case ASC = 'ASC';
    case DESC = 'DESC';

}