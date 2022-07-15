<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Dbal;


use Swift\Dbal\Arguments\Arguments;

interface ResultCollectionInterface extends \Swift\Orm\Collection\ArrayCollectionInterface {
    
    /**
     * Get total possible results for query (without pagination)
     *
     * @return int
     */
    public function getTotalCount(): int;
    
    /**
     * @return \Swift\Orm\Dbal\PageInfo
     */
    public function getPageInfo(): PageInfo;
    
    public function getQuery(): \Cycle\ORM\Select;
    
    public function getArguments(): Arguments;
    
}