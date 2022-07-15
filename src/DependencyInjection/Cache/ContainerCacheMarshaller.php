<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection\Cache;


class ContainerCacheMarshaller implements \Symfony\Component\Cache\Marshaller\MarshallerInterface {
    
    /**
     * @inheritDoc
     */
    public function marshall( array $values, ?array &$failed ): array {
        
        return $values;
    }
    
    /**
     * @inheritDoc
     */
    public function unmarshall( string $value ): string {
        
        return $value;
    }
    
}