<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Cache\Adapter;


use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Cache\Traits\FilesystemTrait;

class FilesystemAdapter extends AbstractAdapter implements PruneableInterface {
    
    use FilesystemTrait;
    
    public function __construct( string $namespace = '', int $defaultLifetime = 0, string $directory = null, MarshallerInterface $marshaller = null ) {
        $this->marshaller = $marshaller ?? new DefaultMarshaller();
        parent::__construct( $namespace, $defaultLifetime );
        $this->init( $namespace, $directory );
    }
    
    
    /**
     * {@inheritdoc}
     */
    protected function doSave( array $values, int $lifetime ): array|bool {
        $expiresAt = $lifetime ? ( time() + $lifetime ) : 0;
        $values    = $this->marshaller->marshall( $values, $failed );
        
        foreach ( $values as $id => $value ) {
            if ( ! $this->write( $this->getFile( $id, true ), $expiresAt . "\n" . rawurlencode( $id ) . "\n" . $value, $expiresAt ) ) {
                $failed[] = $id;
            }
        }
        
        if ( $failed && ! is_writable( $this->directory ) ) {
            throw new CacheException( sprintf( 'Cache directory is not writable (%s).', $this->directory ) );
        }
        
        if ( is_null( $failed ) ) {
            return false;
        }
        
        return $failed;
    }
    
}
