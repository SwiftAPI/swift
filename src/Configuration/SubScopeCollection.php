<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;

use Swift\DependencyInjection\Attributes\Autowire;

#[Autowire]
class SubScopeCollection {
    
    /** @var \Swift\Configuration\ConfigurationSubScopeInterface[] $subScopes */
    private readonly array $subScopes;
    
    public function getAll(): array {
        return $this->subScopes;
    }
    
    public function has( string $scope ): bool {
        foreach ($this->subScopes as $scopeItem) {
            $scopeNames = is_array($scopeItem->getScope()) ? $scopeItem->getScope() : [$scopeItem->getScope()];
            if (in_array($scope, $scopeNames, true)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @param string $scope
     *
     * @return \Swift\Configuration\ConfigurationSubScopeInterface[]
     */
    public function get( string $scope ): array {
        $scopes = [];
        foreach ($this->subScopes as $scopeItem) {
            $scopeNames = is_array($scopeItem->getScope()) ? $scopeItem->getScope() : [$scopeItem->getScope()];
            if (in_array($scope, $scopeNames, true)) {
                $scopes[] = $scopeItem;
            }
        }
    
        return $scopes;
    }
    
    #[Autowire]
    public function setSubScopes( #[Autowire( tag: DiTags::CONFIGURATION_SUB_SCOPE )] ?iterable $subScopes ): void {
        if ( ! $subScopes ) {
            $this->subScopes = [];
            
            return;
        }
        
        $this->subScopes = iterator_to_array( $subScopes, false );
    }
    
}