<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Container\CompilerPass;

use Swift\Kernel\Container\Container;
use Swift\Kernel\KernelDiTags;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ExtensionsCompilerPass
 * @package Swift\Kernel\Container\CompilerPass
 */
class ExtensionsCompilerPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ) {
        /** @var Container $container */
        foreach ($container->getServicesByTag(KernelDiTags::COMPILER_PASS) as $pass) {
            /** @var CompilerPassInterface $pass */
            $compilerPass = $container->get($pass);
            $compilerPass?->process($container);
        }
    }
}