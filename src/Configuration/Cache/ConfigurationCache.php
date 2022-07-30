<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration\Cache;


class ConfigurationCache extends \Swift\Cache\AbstractCache {
    
    public function getNameSpace(): string {
        return 'configuration';
    }
    
    public function getName(): string {
        return 'compiled';
    }
    
}