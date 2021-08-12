<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Mapping;

/**
 * Class Index
 * @package Swift\Model\Mapping
 */
class Index {

    /**
     * Index constructor.
     *
     * @param string $name
     * @param IndexType $indexType
     * @param Field[] $fields
     */
    public function __construct(
        private string $name,
        private IndexType $indexType,
        private array $fields,
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return IndexType
     */
    public function getIndexType(): IndexType {
        return $this->indexType;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array {
        return $this->fields;
    }

    public function getFieldNames(): array {
        return array_map( static fn(Field $field): string => $field->getDatabaseName(), $this->getFields() );
    }


}