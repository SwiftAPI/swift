<?php declare(strict_types=1);


namespace Swift\Model\Events;

use stdClass;
use Swift\Model\Entity\Entity;
use Symfony\Contracts\EventDispatcher\Event;
use Swift\Kernel\Attributes\DI;

#[DI(exclude: true)]
class EntityOnBeforeLoadEvent extends Event {


    /**
     * EntityOnBeforeLoadEvent constructor.
     *
     * @param Entity $entity
     * @param stdClass|array $request
     */
    public function __construct(
        public Entity $entity,
        public stdClass|array $request,
    ) {
    }
}