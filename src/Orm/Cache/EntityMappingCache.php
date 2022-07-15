<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Cache;


use Swift\Cache\AbstractCache;
use Swift\Cache\DefaultMarshaller;
use Swift\Configuration\ConfigurationInterface;
use Swift\Configuration\Utils;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class EntityMappingCache extends AbstractCache {
    
    
    public function __construct(
        DefaultMarshaller $defaultMarshaller,
        protected ConfigurationInterface $configuration,
    ) {
        $adapters = !Utils::isCacheEnabled($this->configuration) ?  [
            new ArrayAdapter( 0, false, 0, 0 ),
        ] : null;
        
        parent::__construct( 0, $defaultMarshaller, $adapters );
    }
    
    /**
     * @inheritDoc
     */
    public function getNameSpace(): string {
        return 'orm';
    }
    
    public function getName(): string {
        return 'entity_mapping';
    }
    
}