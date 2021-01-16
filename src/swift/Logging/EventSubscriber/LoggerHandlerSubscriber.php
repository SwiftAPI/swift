<?php declare(strict_types=1);


namespace Swift\Logging\EventSubscriber;


use Swift\Configuration\Configuration;
use Swift\Events\EventDispatcher;
use Swift\Logging\Event\OnBeforeLoggerHandlersEvent;
use Swift\Logging\Formatter\LineFormatter;
use Monolog\Handler\NativeMailerHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoggerHandlerSubscriber implements EventSubscriberInterface {

    /**
     * @var Configuration $configuration
     */
    private $configuration;

    /**
     * LoggerHandlerSubscriber constructor.
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
            OnBeforeLoggerHandlersEvent::class => 'OnBeforeLoggerHandlers',
        );
    }

    /**
     * @param OnBeforeLoggerHandlersEvent $event
     * @param string $eventClassName
     * @param EventDispatcher $eventDispatcher
     */
    public function OnBeforeLoggerHandlers( OnBeforeLoggerHandlersEvent $event, string $eventClassName, EventDispatcher $eventDispatcher ) {
        if ($this->configuration->get('logging.enable_mail', 'root')) {
            if (is_null($this->configuration->get('logging.logging_mail_from', 'root') || is_null($this->configuration->get('logging.logging_mail_to', 'root')))) {
                return;
            }

            $event->addHandler(
                new NativeMailerHandler(
                    $this->configuration->get('logging.logging_mail_to', 'root'),
                    sprintf('Logging: %s', $this->configuration->get('app.name')),
                    $this->configuration->get('logging.logging_mail_from', 'root'),
                    \Monolog\Logger::ERROR
                )
            );
        }
    }

}