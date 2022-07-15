<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Attributes;

use Attribute;
use JetBrains\PhpStorm\Deprecated;

/**
 * Class DI
 * @package Swift\Kernel\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS), Deprecated(reason: 'Moved to new dedicated DependencyInjection component', replacement: \Swift\DependencyInjection\Attributes\DI::class)]
class DI extends \Swift\DependencyInjection\Attributes\DI {



}