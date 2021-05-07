<?php declare(strict_types=1);
/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Events;

use Swift\Events\AbstractEvent;
use Swift\Model\Entity;
use Swift\Model\Entity\Entity as DeprecatedEntity;
use Swift\Kernel\Attributes\DI;

#[DI(exclude: true)]
class EntityOnFieldUnSerializeEvent extends AbstractEvent {

    protected static string $eventDescription = 'Before entity deserializes field';
    protected static string $eventLongDescription = 'Before entity deserializes a given field value. Useful for deserializing data by the provided on a DBField';

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