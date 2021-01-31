<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Container\CompilerPass;

use Swift\Kernel\Container\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class ExtensionsCompilerPass
 * @package Swift\Kernel\Container\CompilerPass
 */
class ExtensionsCompilerPass implements CompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ) {
        /** @var Container $container */
        foreach ($container->getServicesByTag('kernel.compiler_pass') as $pass) {
            /** @var CompilerPassInterface $pass */
            $compilerPass = $container->get($pass);
            $compilerPass?->process($container);
        }
    }
}