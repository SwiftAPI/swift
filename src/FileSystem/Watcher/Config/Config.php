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

#[DI( autowire: false )]
final class Config {
    
    public const DEFAULT_DELAY_IN_SECONDS = 0.25;
    
    public function __construct(
        private readonly float $delay = self::DEFAULT_DELAY_IN_SECONDS,
        private readonly array $arguments = [],
        private readonly bool $spinnerDisabled = false,
        private readonly WatchList $watchList = new WatchList(),
    ) {
    }
    
    public static function fromArray( array $values ): self {
        return new self(
            $values[ 'delay' ] ?? self::DEFAULT_DELAY_IN_SECONDS,
            $values[ 'arguments' ] ?? [],
            $values[ 'no-spinner' ] ?? false,
            new WatchList(
                $values[ 'watch' ] ?? [],
                $values[ 'extensions' ] ?? [],
                $values[ 'ignore' ] ?? []
            )
        );
    }
    
    public function watchList(): WatchList {
        return $this->watchList;
    }
    
    public function delay(): float {
        return $this->delay;
    }
    
    public function spinnerDisabled(): bool {
        return $this->spinnerDisabled;
    }
    
    public function merge( self $another ): self {
        return new self(
            $this->delay === self::DEFAULT_DELAY_IN_SECONDS && $another->delay ? $another->delay : $this->delay,
            empty( $this->arguments ) && ! empty( $another->arguments ) ? $another->arguments : $this->arguments,
            $another->spinnerDisabled ?: $this->spinnerDisabled,
            $another->watchList->merge( $this->watchList )
        );
    }
    
}