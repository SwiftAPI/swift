<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Voter;


enum Vote: string {
    
    case ACCESS_GRANTED = 'ACCESS_GRANTED';
    case ACCESS_DENIED = 'ACCESS_DENIED';
    case ACCESS_ABSTAIN = 'ACCESS_ABSTAIN';
    
}