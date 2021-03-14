<?php


namespace Foo\Event;

use Symfony\Contracts\EventDispatcher\Event;

class OnBeforeFooEvent extends Event {

    /**
     * @var array $handlers   associative array of handlers
     */
    private $bars;

    /**
     * OnBeforeSyncEvent constructor.
     *
     * @param array $bars
     */
    public function __construct( array $bars = array() ) {
        $this->bars = $bars;
    }

    /**
     * @param string $bar
     */
    public function addBar(string $bar = ''): void {
        $this->bars[] = $bar;
    }

    /**
     * @return array
     */
    public function getBars(): array {
        return $this->bars;
    }

}