<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl;

use Swift\GraphQl\TypeRegistry\QueryRegistry;
use Swift\HttpFoundation\ParameterBag;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;

/**
 * Class TypeRegistryPool
 * @package Swift\GraphQl
 */
#[DI(exclude: false), Autowire]
class TypeRegistryPool extends ParameterBag {

    public function compile(): void {
        foreach ($this->parameters as /** @var TypeRegistryInterface */ $parameter) {
            $parameter->compile();
        }
    }

    #[Autowire]
    public function setTypeRegistries( #[Autowire(tag: 'graphql.type_registry')] iterable $typeRegistries ): void {
        foreach ($typeRegistries as $typeRegistry) {
            $this->parameters[$typeRegistry::class] = $typeRegistry;
        }
    }

}