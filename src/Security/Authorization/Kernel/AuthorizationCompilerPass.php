<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Kernel;

use Swift\Kernel\Attributes\DI;
use Swift\Kernel\DiTags;
use Swift\Security\Authorization\Attributes\IsGranted;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class AuthorizationCompilerPass
 * @package Swift\GraphQl\Kernel
 */
#[DI(tags: [DiTags::COMPILER_PASS])]
class AuthorizationCompilerPass implements CompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ): void {
        foreach ($container->getDefinitions() as $definition) {
            $classReflection = $container->getReflectionClass($definition->getClass());

            foreach ($classReflection?->getMethods() as $reflectionMethod) {
                if (!empty($reflectionMethod->getAttributes(name: IsGranted::class))) {
                    // TODO: Make magic happen
                }
            }
        }
    }
}