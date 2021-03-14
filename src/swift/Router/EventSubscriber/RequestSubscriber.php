<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\EventSubscriber;

use Dibi\Exception;
use Swift\HttpFoundation\ServerRequest;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Event\KernelRequestEvent;
use Swift\Router\HTTPRequest;
use Swift\Router\Model\Request as RequestModel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RequestSubscriber
 * @package Swift\Router\EventSubscriber
 */
#[Autowire]
class RequestSubscriber implements EventSubscriberInterface {

    /**
     * RequestSubscriber constructor.
     *
     * @param HTTPRequest $HTTPRequest
     * @param RequestModel $modelRequest
     */
    public function __construct(
        private HTTPRequest $HTTPRequest,
        private RequestModel $modelRequest
    ) {
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
            KernelRequestEvent::class => 'onKernelRequest',
        );
    }

    /**
     * @param KernelRequestEvent $event
     * @param string $eventClassName
     * @param EventDispatcher $eventDispatcher
     *
     * @return void
     * @throws Exception
     */
    public function onKernelRequest( KernelRequestEvent $event, string $eventClassName, EventDispatcher $eventDispatcher ): void {
        $this->logRequest($event->getRequest());
    }

    /**
     * Method to log a request
     *
     * @param ServerRequest $request
     *
     * @throws \Exception
     */
    private function logRequest(ServerRequest $request): void {

        $ip         = $request->getClientIp();
        $origin     = $request->getUri()->getPath();
        $time       = date('Y-m-d H:i:s' );
        $method     = $request->getMethod();
        $headers    = $request->getHeaders()->all();
        $body       = $request->getBody()->getContents();
        $code       = 200;

        $this->modelRequest->logRequest($ip, $origin, $time, $method, $headers, $body, $code);
    }

}