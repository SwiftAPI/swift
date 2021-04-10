<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Loaders;


use GraphQL\Type\Definition\Directive;
use Swift\GraphQl\LoaderInterface;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class DirectivesLoader
 * @package Swift\GraphQl\Loaders
 */
#[Autowire]
class DirectivesLoader implements LoaderInterface {

    private array $directives = array();

    /**
     * DirectivesLoader constructor.
     *
     * @param TypeRegistryInterface $directiveRegistry
     */
    public function __construct(
        private TypeRegistryInterface $directiveRegistry,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function load( TypeRegistryInterface $typeRegistry ): void {
        $this->directives = array_merge($this->directives, Directive::getInternalDirectives());
        $this->directiveRegistry->addDirectives( $this->directives );
    }

    #[Autowire]
    public function setDirectives( #[Autowire(tag: 'graphql.directive')] iterable $directives ): void {
        foreach ($directives as $directive) {
            $this->directives[] = $directive;
        }
    }
}