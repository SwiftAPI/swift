<?php declare(strict_types=1);


namespace Swift\Model\Events;

use Swift\Model\Entity;
use Swift\Model\Entity\Entity as DeprecatedEntity;
use Symfony\Contracts\EventDispatcher\Event;
use Swift\Kernel\Attributes\DI;

#[DI(exclude: true)]
class EntityOnFieldSerializeEvent extends Event {

    /**
     * OnFieldSerializeEvent constructor.
     *
     * @param Entity|DeprecatedEntity $entity
     * @param string $action
     * @param string $name
     * @param mixed $value
     */
    public function __construct(
        public Entity|DeprecatedEntity $entity,
        public string $action,
        public string $name,
        public mixed $value,
    ) {
    }
}