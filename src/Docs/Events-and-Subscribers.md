## 8. Events & Subscribers
Under the hood the [Symfony Event Dispatcher](https://symfony.com/doc/current/components/event_dispatcher.html) is used, however there is a custom implementation on the Event Dispatcher. This is in order to provide future stability and to enable the system for adding functionality in to the event system.
### Default system events
### How to subscribe to events
When you want to do something when a given event occurs (like logging, or for example add a Route variable_type) you can subscribe to those event using a EventSubscriber instance. In contrary to Symfony, in this system Event Subscriber do support Dependency Injection. It is recommend to only use subscribers to 'catch' the event and use a service to execute the actual logic (and if applicable apply the result to the event). Pretty the same as you would do in a Controller or a command. This makes the logic in the service resuable for different occasions and keeps the subscriber clean.

```php
namespace Foo\EventSubscriber;

use Henri\Framework\Configuration\Configuration;
use Henri\Framework\Events\EventDispatcher;
use Henri\Framework\Router\Event\OnBeforeRouteEnterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FooSubscriber implements EventSubscriberInterface {

    /**
     * @var Configuration $configuration
     */
    private $configuration;

    /**
     * FooSubscriber constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct( Configuration $configuration ) {
        $this->configuration = $configuration;
    }


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array {
        return array(
            OnBeforeRouteEnterEvent::class => 'onBeforeRouteEnter',
        );
    }

    /**
     * @param OnBeforeRouteEnterEvent $event
     * @param string $eventClassName
     * @param EventDispatcher $eventDispatcher
     */
    public function onBeforeRouteEnter( OnBeforeRouteEnterEvent $event, string $eventClassName, EventDispatcher $eventDispatcher ) {
        // Read/modify event data or do some logging
    }

}
```
### How to create your own events
Events are classes which can be dispatched using the EventDispatcher. You can easily create your own like the example.
```php
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
```

### Dispatch events
Events are dispatched using the EventDispatcher. You will need to inject the EventDispatcher (`Henri\Framework\Events\EventDispatcher`) into your class. Below an example of how the Router uses an event to provide all found routes and match types, allows for all subscribers to modify those and reassign them before actually matching the routes.
```php
/**
 * Get route from current url
 *
 * @return Route
 * @throws Exception
 */
public function getCurrentRoute(): Route {
	$this->routeHarvest = $this->harvester->harvestRoutes();

	/**
	 * @var OnBeforeRoutesCompileEvent $onBeforeCompileRoutes
	 */
	$onBeforeCompileRoutes = $this->dispatcher->dispatch(
	    new OnBeforeRoutesCompileEvent($this->routeHarvest, $this->matchTypes),
	    OnBeforeRoutesCompileEvent::class
	);

	/**
	 * Reassign possibly changed routes and match types
	 */
	$this->routeHarvest = $onBeforeCompileRoutes->getRoutes();
	$this->matchTypes   = $onBeforeCompileRoutes->getMatchTypes();

	$this->bindRoutes();
	$match = $this->match();

	if (is_null($match)) {
		throw new NotFoundException('Not found');
	}

	return $match;
}
```

&larr; [Annotations](https://github.com/HenrivantSant/henri/blob/master/Docs/Annotations.md#7-annotations) | [Logging](https://github.com/HenrivantSant/henri/blob/master/Docs/Logging.md#logging) &rarr;