<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Arguments;

use Dibi\Fluent;

/**
 * Interface ArgumentInterface
 * @package Swift\Model\Arguments
 */
interface ArgumentInterface {

    /**
     * Apply argument to query
     *
     * @param Fluent $query
     *
     * @return Fluent
     */
    public function apply( Fluent $query ): Fluent;

}