<?php declare(strict_types=1);


namespace Swift\Logging\Event;


use Monolog\Handler\AbstractProcessingHandler;
use Symfony\Contracts\EventDispatcher\Event;

class OnBeforeLoggerHandlersEvent extends Event {

    /**
     * @var array Array of handlers assigned to the logger
     */
    private $handlers;

    /**
     * @var string Logger name
     */
    public $name;

    /**
     * OnBeforeLoggerHandlers constructor.
     *
     * @param array $handlers
     */
    public function __construct( string $name = null, array $handlers = array() ) {
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