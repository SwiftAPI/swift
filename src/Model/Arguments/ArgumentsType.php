<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Arguments;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\InputType;
use Swift\GraphQl\Utils;
use Swift\Model\Entity\Arguments;
use TypeError;

/**
 * Class ArgumentsType
 * @package Swift\Model\Entity
 */
#[InputType( name: 'Arguments' )]
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
        #[Field( description: 'Number of edges to load' )] public int|null $first = null,
        #[Field( description: 'Number of edges to load in reverse' )] public int|null $last = null,
        #[Field( description: 'Load all edges before cursor' )] public string|null $before = null,
        #[Field( description: 'Load all edges after cursor' )] public string|null $after = null,
        #[Field( description: 'Order edges by one of the options' )] public string|null $orderBy = null,
        public string|null $groupBy = null,
        #[Field( type: ArgumentDirectionEnum::class, description: 'Sorting direction. Note that backward pagination will automatically be DESC' )] public string|null $direction = null,
    ) {
        if (is_null($this->first) && is_null($this->last)) {
            $this->first = 25;
        }
        if ($this->before) {
            $this->before = Utils::decodeCursor($this->before);
        }
        if ($this->after) {
            $this->after = Utils::decodeCursor($this->after);
        }
        if ( $this->direction && ! ArgumentDirectionEnum::isValid( $this->direction ) ) {
            throw new TypeError( sprintf( 'Expected one of the following types (%s) for argument $direction, instead got: %s', implode( separator: ', ', array: ArgumentDirectionEnum::keys() ), $this->direction ) );
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
            $arguments->limit = $this->first;
        }
        if ($this->last) {
            $arguments->limit = $this->last;
            $arguments->direction = ArgumentDirectionEnum::DESC;
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