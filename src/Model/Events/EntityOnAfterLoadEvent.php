<?php declare(strict_types=1);


namespace Swift\Model\Events;

use stdClass;
use Swift\Model\Entity\Entity;
use Symfony\Contracts\EventDispatcher\Event;
use Swift\Kernel\Attributes\DI;

#[DI(exclude: true)]
class EntityOnAfterLoadEvent extends Event {

    /**
     * EntityOnAfterLoadEvent constructor.
     *
     * @param Entity $entity
     * @param stdClass|array $request
     * @param stdClass $response
     */
    public function __construct(
        public Entity $entity,
        public stdClass|array $request,
        public stdClass $response,
    ) {
    }
}