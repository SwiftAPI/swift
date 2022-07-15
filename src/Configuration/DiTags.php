<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;

use Swift\Kernel\TypeSystem\Enum;

/**
 * Class DiTags
 * @package Swift\Configuration
 */
class DiTags extends Enum {

    public const CONFIGURATION = 'configuration.scope';
    public const CONFIGURATION_SUB_SCOPE = 'CONFIGURATION.SUB.SCOPE';

}