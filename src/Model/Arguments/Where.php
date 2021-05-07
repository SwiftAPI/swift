<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Arguments;

use Dibi\Fluent;
use Swift\Kernel\Attributes\DI;
use Swift\Model\Types\ArgumentComparisonTypesEnum;

/**
 * Class Where
 * @package Swift\Model\Arguments
 */
#[DI(autowire: false)]
class Where implements ArgumentInterface {

    public const GREATER_THAN = '>';
    public const LESS_THAN = '<';
    public const EQUALS = '=';

    /**
     * Where constructor.
     *
     * @param string $fieldName
     * @param string $comparison
     * @param mixed $value
     */
    public function __construct(
        private string $fieldName,
        private string $comparison,
        private mixed $value,
    ) {
        new ArgumentComparisonTypesEnum($this->comparison);
    }

    /**
     * Apply query
     *
     * @param Fluent $query
     * @param array $properties
     *
     * @return Fluent
     */
    public function apply( Fluent $query, array $properties ): Fluent {
        return $query->where($properties[$this->fieldName] . ' ' . $this->comparison . ' %s ', $this->value);
    }

}