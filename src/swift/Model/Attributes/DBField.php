<?php declare(strict_types=1);

namespace Swift\Model\Attributes;

use Attribute;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\TypeSystem\Enum;
use Swift\Model\Types\FieldTypes;
use InvalidArgumentException;

/**
 * Class DBField
 * @package Swift\Model\Attributes
 */
#[Attribute(Attribute::TARGET_PROPERTY), DI(exclude: true)]
class DBField {

    /**
     * DBField constructor.
     *
     * @param string $name name of the db field
     * @param bool $primary whether this field is the primary key
     * @param string $type
     * @param array $serialize
     * @param int $length
     * @param bool $empty whether field is nullable (defaults to false)
     * @param bool $unique whether to add a unique constraint to the field
     * @param string|null $enum optional enum to validate
     */
    public function __construct(
        public string $name,
        public bool $primary = false,
        public string $type = FieldTypes::TEXT,
        public array $serialize = array(),
        public int $length = 0,
        public bool $empty = false,
        public bool $unique = false,
        public bool $index = false,
        public ?string $enum = null,
    ) {
        if (!is_null($this->enum) && (!$this->enum instanceof Enum)) {
            throw new InvalidArgumentException(sprintf('%s should be a valid instance of %s', $this->enum, Enum::class));
        }
    }
}