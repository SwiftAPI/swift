<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection\CompilerPass;

use Swift\DependencyInjection\Attributes\DI;
use Swift\DependencyInjection\Container;
use Swift\Kernel\KernelDiTags;

/**
 * Interface CompilerPassInterface
 * @package Swift\DependencyInjection\CompilerPass
 */
#[DI(tags: [KernelDiTags::POST_COMPILER_PASS])]
interface PostCompilerPassInterface {

    public function process( Container $container );

}