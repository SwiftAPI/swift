<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session\Storage\Handler;

use Symfony\Component\Cache\Marshaller\MarshallerInterface;

/**
 * @author Ahmed TAILOULOUTE <ahmed.tailouloute@gmail.com>
 */
class IdentityMarshaller implements MarshallerInterface {

    /**
     * {@inheritdoc}
     */
    public function marshall( array $values, ?array &$failed ): array {
        foreach ( $values as $key => $value ) {
            if ( ! \is_string( $value ) ) {
                throw new \LogicException( sprintf( '%s accepts only string as data.', __METHOD__ ) );
            }
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function unmarshall( string $value ): string {
        return $value;
    }
}