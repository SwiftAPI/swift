<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal\Arguments;

use Swift\Orm\Entity\Arguments;


class ArgumentsType {

    /**
     * ArgumentsType constructor.
     *
     * @param int|null $first Number of edges to load
     * @param int|null $last Number of edges to load in reverse
     * @param string|null $before Load all edges before cursor
     * @param string|null $after Load all edges after cursor
     * @param string|null $orderBy
     * @param string|null $groupBy
     * @param string|null $direction
     */
    public function __construct(
        public int|null $first = null,
        public int|null $last = null,
        public string|null $before = null,
        public string|null $after = null,
        public string|null $orderBy = null,
        public string|null $groupBy = null,
        public string|null $direction = null,
    ) {
        if ( ($this->first === null) &&( $this->last === null) ) {
            $this->first = 25;
        }
    }

    /**
     * Convert to Arguments Model
     *
     * @return Arguments
     */
    public function toArgument(): Arguments {
        $arguments = new Arguments();
        if ($this->first) {
            $arguments->setLimit($this->first);
        }
        if ($this->last) {
            $arguments->setLimit($this->last);
            $arguments->setDirection( ArgumentDirection::DESC );
        }
        if ($this->before) {
            $cursor = is_numeric($this->before) ? (int) $this->before : $this->before;
            $arguments->addArgument(new Where('id', Where::LESS_THAN, $cursor));
        }
        if ($this->after) {
            $cursor = is_numeric($this->after) ? (int) $this->after : $this->after;
            $arguments->addArgument(new Where('id', Where::GREATER_THAN, $cursor));
        }

        return $arguments;
    }


}