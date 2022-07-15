<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Behavior;

use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Registry;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Swift\Orm\Behavior\SchemaModifierInterface;

/**
 * EventListener adds a custom listener to the ORM schema. Allows you to create your own behaviors.
 * A custom listener can accept the required dependencies in the constructor, implement one or more methods
 * that receive the event as the first parameter and can work with him.
 * The behavior has two parameters:
 *   - listener - listener class
 *   - args - array with additional parameters
 *
 * A special attribute must be added to the listener methods to indicate which event to listen for.
 * For example:
 * #[Listen(OnCreate::class)]
 * public function method(OnCreate $event): void
 * {
 *    //
 * }
 */
#[\Attribute( \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE ), NamedArgumentConstructor]
final class EventListener implements SchemaModifierInterface {
    
    private string $role;
    
    /**
     * @psalm-param class-string $listener
     */
    public function __construct(
        private readonly string $listener,
        private readonly array  $args = []
    ) {
    }
    
    public function compute( Registry $registry ): void {
    }
    
    public function render( Registry $registry ): void {
    }
    
    public function modifySchema( array &$schema ): void {
        $schema[ SchemaInterface::LISTENERS ][] = $this->args === [] ? $this->listener : [ $this->listener, $this->args ];
    }
    
    final public function withRole( string $role ): static {
        $clone       = clone $this;
        $clone->role = $role;
        
        return $clone;
    }
}
