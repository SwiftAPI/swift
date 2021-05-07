<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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
     * @param bool $index
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
        if (!is_null($this->enum) && (!is_a($this->enum, Enum::class, true))) {
            throw new InvalidArgumentException(sprintf('%s should be a valid instance of %s', $this->enum, Enum::class));
        }
    }

    public function toObject(): \stdClass {
        $object = new \stdClass();
        foreach (get_object_vars($this) as $name => $var) {
            $object->{$name} = $var;
        }

        return $object;
    }
}