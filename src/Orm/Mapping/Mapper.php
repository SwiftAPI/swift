<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping;




class Mapper extends \Cycle\ORM\Mapper\Mapper {
    
    public function setEntityFactory( \Cycle\ORM\Mapper\Proxy\ProxyEntityFactory $proxyEntityFactory ): void {
        $this->entityFactory = $proxyEntityFactory;
    }
    
}