<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Attributes;

use Swift\Kernel\Attributes\DI;
use Swift\Model\EntityInterface;

/**
 * Connect Many to One relationship between entities
 *
 * Class ManyToOne
 * @package Swift\Model\Attributes
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), DI(autowire: false)]
final class ManyToOne {

    /**
     * Join constructor.
     *
     * @param string $entity Entity to join
     * @param string $joiningEntityField Field name of entity that's joined
     * @param string $currentEntityField Field name in this entity to match join on
     */
    public function __construct(
        public string $entity,
        public string $joiningEntityField,
        public string $currentEntityField,
    ) {
        if (!is_a($this->entity, EntityInterface::class, true)) {
            throw new \InvalidArgumentException(sprintf('Cannot use %s as and Entity relation as this class does not implement %s', $this->entity, EntityInterface::class));
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