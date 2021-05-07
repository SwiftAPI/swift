<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Logging\EventSubscriber;


use Swift\Configuration\ConfigurationInterface;
use Swift\Events\EventDispatcher;
use Swift\Kernel\Attributes\Autowire;
use Swift\Logging\Event\OnBeforeLoggerHandlersEvent;
use Monolog\Handler\NativeMailerHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class LoggerHandlerSubscriber
 * @package Swift\Logging\EventSubscriber
 */
#[Autowire]
class LoggerHandlerSubscriber implements EventSubscriberInterface {

    /**
     * @var ConfigurationInterface $configuration
     */
    private ConfigurationInterface $configuration;

    /**
     * LoggerHandlerSubscriber constructor.
     *
     * @param ConfigurationInterface $configuration
     */
    public function __construct( ConfigurationInterface $configuration ) {
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
                    sprintf('Logging: %s', $this->configuration->get('app.name', 'app')),
                    $this->configuration->get('logging.logging_mail_from', 'root'),
                    \Monolog\Logger::ERROR
                )
            );
        }
    }

}