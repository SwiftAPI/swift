<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\Config;

use Swift\DependencyInjection\Attributes\DI;
use Symfony\Component\Console\Input\InputInterface;

#[DI( autowire: false )]
final class InputExtractor {
    
    public function __construct(
        private readonly InputInterface $input
    ) {
    }
    
    public function getStringArgument( string $key, string $default = null ): ?string {
        $argument = $this->input->getArgument( $key );
        
        return $this->stringValueOrDefault( $argument, $default );
    }
    
    public function getStringOption( string $key, string $default = null ): ?string {
        $option = $this->input->getOption( $key );
        
        return $this->stringValueOrDefault( $option, $default );
    }
    
    private function stringValueOrDefault( $value, string $default = null ): ?string {
        if ( $value === null ) {
            return $default;
        }
        
        if ( is_array( $value ) && isset( $value[ 0 ] ) ) {
            return (string) $value[ 0 ];
        }
        
        return (string) $value;
    }
    
    public function getArrayOption( string $key ): array {
        $option = $this->input->getOption( $key );
        
        if ( is_string( $option ) && ! empty( $option ) ) {
            return explode( ',', $option );
        }
        
        if ( ! is_array( $option ) ) {
            return [];
        }
        
        return empty( $option ) ? [] : $option;
    }
    
    public function getFloatOption( string $key ): float {
        return (float) $this->input->getOption( $key );
    }
    
    public function getBooleanOption( string $key ): bool {
        return (bool) $this->input->getOption( $key );
    }
    
}