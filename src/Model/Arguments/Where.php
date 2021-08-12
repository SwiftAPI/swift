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
use Swift\Model\Mapping\Table;
use Swift\Model\Query\QueryBuilder;

/**
 * Class Where
 * @package Swift\Model\Arguments
 */
#[DI(autowire: false)]
class Where implements ArgumentInterface {

    public const GREATER_THAN = '>';
    public const LESS_THAN = '<';
    public const EQUALS = '=';
    public const LIKE = 'LIKE';

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
     * @param \Swift\Model\Query\QueryBuilder $query
     * @param \Swift\Model\Mapping\Table      $table
     *
     * @return QueryBuilder
     */
    public function apply( QueryBuilder $query, Table $table ): QueryBuilder {
        $value = $this->value;

        if ($this->comparison === static::LIKE) {
            $value = '%' . $value . '%';
        }

        return $query->where($table->getFieldByPropertyName($this->fieldName)->getDatabaseName() . ' ' . $this->comparison . ' %s ', $value);
    }

}