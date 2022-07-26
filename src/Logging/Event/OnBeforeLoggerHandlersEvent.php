<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Logging\Event;


use Monolog\Handler\AbstractProcessingHandler;
use Swift\Events\AbstractEvent;

/**
 * Class OnBeforeLoggerHandlersEvent
 * @package Swift\Logging\Event
 */
class OnBeforeLoggerHandlersEvent extends AbstractEvent {
    
    protected static string $eventDescription = 'On Logger construction. Optionally add or remove Logger Handlers';
    protected static string $eventLongDescription = 'On Logger construction. Optionally add or remove Logger Handlers';
    
    /**
     * @var array Array of handlers assigned to the logger
     */
    private array $handlers;
    
    /**
     * @var string Logger name
     */
    public string $name;
    
    /**
     * OnBeforeLoggerHandlers constructor.
     *
     * @param string|null $name
     * @param array       $handlers
     */
    public function __construct( string $name = null, array $handlers = [] ) {
        $this->name     = $name;
        $this->handlers = $handlers;
    }
    
    /**
     * @return array
     */
    public function getHandlers(): array {
        return $this->handlers;
    }
    
    /**
     * @param AbstractProcessingHandler $handler
     */
    public function addHandler( AbstractProcessingHandler $handler ): void {
        $this->handlers[] = $handler;
    }
    
    /**
     * @param array $handlers
     */
    public function setHandlers( array $handlers ): void {
        $this->handlers = $handlers;
    }
    
    
}