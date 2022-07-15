<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Cache;


class DefaultMarshaller implements \Symfony\Component\Cache\Marshaller\MarshallerInterface {
    
    /**
     * @inheritDoc
     */
    public function marshall( array $values, ?array &$failed ): array {
        foreach ($values as $key => $value) {
            $values[$key] = serialize( $value );
        }
        
        return $values;
    }
    
    /**
     * @inheritDoc
     */
    public function unmarshall( string $value ): mixed {
        if ('b:0;' === $value) {
            return false;
        }
        if ('N;' === $value) {
            return null;
        }
    
        return unserialize($value, [ 'allowed_classes' => true ]);
    }
}