<?php declare(strict_types=1);


namespace Swift\Model\Entity;

use Dibi\Fluent;
use InvalidArgumentException;
use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\InputType;
use Swift\GraphQl\Attributes\Type;
use Swift\GraphQl\Generators\EntityEnumGenerator;
use Swift\Kernel\Attributes\DI;
use Swift\Model\Types\ArgumentDirectionEnum;
use TypeError;

/**
 * Class Arguments
 * @package Swift\Model\Entity
 */
#[InputType]
class Arguments {

    /**
     * Arguments constructor.
     *
     * @param int|null $offset
     * @param int|null $limit
     * @param string|null $orderBy
     * @param string|null $groupBy
     * @param string|null $direction
     */
    public function __construct(
        #[Field] public int|null $offset = 0,
        #[Field] public int|null $limit = 0,
        #[Field] public string|null $orderBy = null,
        public string|null $groupBy = null,
        #[Field(type: ArgumentDirectionEnum::class)] public string|null $direction = null,
    ) {
        if ($this->direction && !ArgumentDirectionEnum::isValid($this->direction)) {
            throw new TypeError(sprintf('Expected one of the following types (%s) for argument $direction, instead got: %s', implode(separator: ', ', array: ArgumentDirectionEnum::keys()), $this->direction));
        }
    }

    /**
     * @param Fluent $query
     * @param array $properties
     * @param mixed $primaryKey
     */
    public function apply( Fluent $query, array $properties, mixed $primaryKey ): void {
        if ($this->offset && ($this->offset > 0)) {
            $query->offset($this->offset);
        }
        if ($this->limit && ($this->limit > 0)) {
            $query->limit($this->limit);
        }
        if ($this->orderBy) {
            if (!array_key_exists(key: $this->orderBy, array: $properties)) {
                throw new InvalidArgumentException(
                    sprintf('Field %s can not be used for ordering. The following options are available: %s',
                        $this->orderBy, implode(separator: ', ', array: array_keys($properties))
                    ));
            }
            $query->orderBy($this->orderBy);
        }
        if ($this->groupBy) {
            if (!array_key_exists(key: $this->groupBy, array: $properties)) {
                throw new InvalidArgumentException(
                    sprintf('Field %s can not be used for grouping. The following options are available: %s',
                        $this->groupBy, implode(separator: ', ', array: array_keys($properties))
                    ));
            }
            $query->groupBy($this->groupBy);
        }
        if ($this->direction && ($this->direction === ArgumentDirectionEnum::ASC)) {
            $query->asc();
        }
        if ($this->direction && ($this->direction === ArgumentDirectionEnum::DESC)) {
            if (!$this->orderBy) { // Fallback to primary key sorting
                $query->orderBy($primaryKey);
            }
            $query->desc();
        }
    }
}