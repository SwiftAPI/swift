<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior\Exception\OptimisticLock;

class ChangedVersionException extends OptimisticLockException {
    
    public function __construct( mixed $old, mixed $new ) {
        parent::__construct( sprintf( 'Record version change detected. Old value `%s`, a new value `%s`.', $old, $new ) );
    }
    
}
