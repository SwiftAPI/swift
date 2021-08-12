<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Events;

use Swift\Events\AbstractEvent;
use Swift\Kernel\Attributes\DI;
use Swift\Model\Mapping\Field;

#[DI( autowire: false )]
class EntityOnFieldUnSerializeEvent extends AbstractEvent {

    protected static string $eventDescription = 'Before entity deserializes field';
    protected static string $eventLongDescription = 'Before entity deserializes a given field value. Useful for deserializing data by the provided on a DBField';

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
        public ?Field  $field,
        public mixed  $value,
    ) {
    }


}