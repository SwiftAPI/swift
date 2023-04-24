<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Behavior;

use Swift\Orm\Behavior\Listener\Hook as Listener;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use JetBrains\PhpStorm\ArrayShape;
use Swift\Orm\Behavior\BaseModifier;

/**
 * Hook allows easy listening for any event using callable.
 * The callback function can be a static function in the entity itself or in any other class.
 * As the first parameter, the callback function accepts an event in which
 * you can get the entity class and work with him.
 * The behavior has two parameters:
 *    - callable - callback function
 *    - events - string or array. One or several events during which callable will be called
 */
#[\Attribute( \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE ), NamedArgumentConstructor]
#[\AllowDynamicProperties]
final class Hook extends BaseModifier {
    
    /** @var callable */
    private $callable;
    
    /**
     * @psalm-param callable                                   $callable Callable
     * @psalm-param class-string|non-empty-array<class-string> $events   Listen events
     */
    public function __construct(
        callable             $callable,
        private array|string $events
    ) {
        $this->callable = $callable;
        
        if ( \is_string( $events ) ) {
            $this->events = [ $events ];
        }
    }
    
    protected function getListenerClass(): string {
        return Listener::class;
    }
    
    #[ArrayShape( [ 'callable' => 'callable', 'events' => 'array' ] )]
    protected function getListenerArgs(): array {
        return [
            'callable' => $this->callable,
            'events'   => $this->events,
        ];
    }
}
