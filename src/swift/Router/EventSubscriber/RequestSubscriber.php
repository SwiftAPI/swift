<?php declare(strict_types=1);

namespace Swift\Router\EventSubscriber;

use Dibi\Exception;
use Swift\Kernel\Event\KernelRequestEvent;
use Swift\Router\Exceptions\BadRequestException;
use Swift\Router\Helper\Validator;
use Swift\Router\HTTPRequest;
use Swift\Router\Model\Request as RequestModel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestSubscriber implements EventSubscriberInterface {

    /**
     * RequestSubscriber constructor.
     *
     * @param Validator $requestValidator
     * @param HTTPRequest $HTTPRequest
     * @param RequestModel $modelRequest
     */
    public function __construct(
        private Validator $requestValidator,
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
        $requestValid = $this->requestValidator->requestIsValid();

        $this->logRequest($requestValid);

        if (!$requestValid) {
            throw new BadRequestException('Invalid request');
        }
    }

    /**
     * Method to log a request
     *
     * @param bool $isValidRequest
     *
     * @throws Exception
     */
    private function logRequest(bool $isValidRequest): void {
        $ip         = $this->HTTPRequest->request->getRequest()['REMOTE_ADDR'];
        $origin     = $this->HTTPRequest->request->getRequest()['HTTP_HOST'] . $this->HTTPRequest->request->getUri();
        $time       = date('Y-m-d H:i:s' );
        $method     = $this->HTTPRequest->request->getMethod();
        $headers    = $this->HTTPRequest->request->getHeaders();
        $body       = $this->HTTPRequest->request->input->getArray();
        $code       = $isValidRequest ? 200 : 400;

        $this->modelRequest->logRequest($ip, $origin, $time, $method, $headers, $body, $code);
    }

}