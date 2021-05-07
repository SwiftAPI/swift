<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Attributes;

use Swift\Model\EntityInterface;
use Swift\Model\Types\TableJoinTypesEnum;

/**
 * Class DBJoin
 * @package Swift\Model\Attributes
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DBJoin {

    /**
     * DBJoin constructor.
     *
     * @param string $entity Entity to join
     * @param string $joiningEntityField Field name of entity that's joined
     * @param string $currentEntityField Field name in this entity to match join on
     */
    public function __construct(
        public string $entity,
        public string $joiningEntityField,
        public string $currentEntityField,
        //public string $type = TableJoinTypesEnum::INNER,
    ) {
        //$this->type = (new TableJoinTypesEnum($this->type))->getValue();
        if (!is_a($this->entity, EntityInterface::class, true)) {
            throw new \InvalidArgumentException(sprintf('Cannot use %s as join as this class does not implement %s', $this->entity, EntityInterface::class));
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