<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Events;

use Swift\Events\AbstractEvent;
use Swift\Model\Mapping\Field;
use Swift\Kernel\Attributes\DI;

/**
 * Class EntityOnFieldSerializeEvent
 * @package Swift\Model\Events
 */
#[DI(autowire: false)]
class EntityOnFieldSerializeEvent extends AbstractEvent {

    protected static string $eventDescription = 'Before entity serializes field';
    protected static string $eventLongDescription = 'Before entity serializes a given field value. Useful for serializing data by the provided on a DBField';

    /**
     * OnFieldSerializeEvent constructor.
     *
     * @param string                          $entity
     * @param string                          $actionOrType
     * @param \Swift\Model\Mapping\Field|null $field
     * @param mixed                           $value
     */
    public function __construct(
        public string $entity,
        public string $actionOrType,
        public ?Field  $field = null,
        public mixed $value = null,
    ) {
    }


}