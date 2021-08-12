<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Types;

use Swift\Events\EventDispatcher;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\KernelDiTags;
use Swift\Model\Events\EntityOnFieldSerializeEvent;
use Swift\Model\Events\EntityOnFieldUnSerializeEvent;
use Swift\Model\Mapping\Field;

/**
 * Class TypeTransformer
 * @package Swift\Model\Types
 */
#[Autowire]
class TypeTransformer {

    /** @var TypeInterface[] $types */
    private array $types;

    public function __construct(
        private EventDispatcher $dispatcher,
    ) {
    }

    public function getType( string $typeName ): ?TypeInterface {
        return $this->types[ $typeName ] ?? new UnknownType( $typeName );
    }

    public function transformToPhpValue( string $typeName, mixed $value, string $entity, ?Field $field ): mixed {
        /** @var \Swift\Model\Events\EntityOnFieldUnSerializeEvent $result */
        $result = $this->dispatcher->dispatch( new EntityOnFieldUnSerializeEvent( $entity, $typeName, $field, $value ) );
        return array_key_exists( $typeName, $this->types ) ? $this->types[ $typeName ]->transformToPhpValue( $result->value ) : $value;
    }

    public function transformToDatabaseValue( string $typeName, mixed $value, string $entity, ?Field $field ): mixed {
        /** @var \Swift\Model\Events\EntityOnFieldSerializeEvent $result */
        $result = $this->dispatcher->dispatch( new EntityOnFieldSerializeEvent( $entity, $typeName, $field, $value ) );
        return array_key_exists( $typeName, $this->types ) ? $this->types[ $typeName ]->transformToDatabaseValue( $result->value ) : $value;
    }

    #[Autowire]
    public function setTypes( #[Autowire( tag: KernelDiTags::ENTITY_TYPE )] iterable $types ): void {
        foreach ( $types as /** @var TypeInterface $type */ $type ) {
            if ( isset( $this->types ) && array_key_exists( $type->getName(), $this->types ) ) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Found duplicate Entity Type Transformer (%s), already has %s and tried to declare %s. Both try to resolve type "%s"',
                        TypeInterface::class,
                        $this->types[ $type->getName() ]::class,
                        $type::class,
                        $type->getName(),
                    ) );
            }

            $this->types[ $type->getName() ] = $type;
        }
    }

}