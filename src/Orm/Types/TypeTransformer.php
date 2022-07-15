<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Types;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\KernelDiTags;

/**
 * Class TypeTransformer
 * @package Swift\Orm\Types
 */
#[Autowire]
class TypeTransformer {
    
    /** @var TypeInterface[] $types */
    private array $types;
    
    public function getType( string $typeName ): ?TypeInterface {
        return $this->types[ strtolower($typeName) ] ?? new UnknownType( $typeName );
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
                    )
                );
            }
            
            $this->types[ strtolower($type->getName()) ] = $type;
            $this->types[ $type::class ]     = $type;
        }
    }
    
}