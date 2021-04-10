# HTTP Foundation
The HTTP Foundation component contains all logic for request, responses and (in the future) sessions and cookies. HTTP Foundation is fully [PSR-7](https://www.php-fig.org/psr/psr-7/) compliant and therefore easily compatible with third party libraries implementing the same interface.

## Request
The request represents the request made to the application. In a controller this a accessible through `$this->getRequest()`. If you need to inject the request in a service inject it as `Swift\HttpFoundation\RequestInterface $request` to avoid a long chain of passing it through from the controller.

## Response
Responses are returned from controller methods and must implement the [PSR-7](https://www.php-fig.org/psr/psr-7/) compliant `Swift\HttpFoundation\ResponseInterface`. Swift comes out of the box with the following responses:
- ``Swift\HttpFoundation\Response``
- ``Swift\HttpFoundation\JsonResponse``
- ``Swift\HttpFoundation\RedirectResponse``
- ``Swift\HttpFoundation\BinaryFileResponse``

More on the use of responses and controllers in Routing and Controllers.

## Session
In active development

## Cookies
In active development

&larr; [Users](https://github.com/HenrivantSant/henri/blob/master/Docs/Users.md#users)