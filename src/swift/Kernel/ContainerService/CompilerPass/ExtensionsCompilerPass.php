<?php declare(strict_types=1);

namespace Swift\Kernel\ContainerService\CompilerPass;

use Swift\Kernel\ContainerService\ContainerService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ExtensionsCompilerPass implements CompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ) {
        /** @var ContainerService $container */
        foreach ($container->getDefinitionsByTag('kernel.compiler_pass') as $pass) {
            /** @var CompilerPassInterface $pass */
            $compilerPass = $container->get($pass);
            $compilerPass?->process($container);
        }
    }
}