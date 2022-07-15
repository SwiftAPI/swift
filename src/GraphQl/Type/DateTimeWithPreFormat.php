<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Type;


class DateTimeWithPreFormat extends \DateTimeImmutable {
    
    public function __construct(
        string                    $time,
        protected readonly string $format,
    ) {
        parent::__construct( $time );
    }
    
    public function format( string $format = '' ): string {
        return parent::format( $this->format );
    }
    
    public function __toString(): string {
        return $this->format();
    }
    
    
}