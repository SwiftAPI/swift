<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal\Arguments;

use Swift\Orm\Mapping\Definition\Entity;
use Swift\Dbal\QueryBuilder;

/**
 * Interface ArgumentInterface
 * @package Swift\Orm\Arguments
 */
interface ArgumentInterface {
    
    /**
     * Apply argument to query
     *
     * @param \Cycle\ORM\Select        $query
     * @param \Swift\Orm\Mapping\Definition\Entity $entity
     *
     * @return \Cycle\ORM\Select
     */
    public function apply( \Cycle\ORM\Select $query, Entity $entity ): \Cycle\ORM\Select;
    
}