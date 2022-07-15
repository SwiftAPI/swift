<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;


use Swift\DependencyInjection\Attributes\DI;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[DI( tags: [DiTags::CONFIGURATION_SUB_SCOPE ])]
interface ConfigurationSubScopeInterface {
    
    public function getScope(): string|array;
    
    public function getConfigTreeBuilder( NodeBuilder $builder ): NodeBuilder;
    
    public function getDefaultValues(): array;
    
}