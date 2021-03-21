<?php declare(strict_types=1);

namespace Foo\Controller;

use Foo\Service\FooService;
use Swift\Configuration\ConfigurationInterface;
use Swift\Controller\AbstractController;
use Swift\HttpFoundation\JsonResponse;
use Swift\Router\Attributes\Route;
use Swift\Router\RouteParameterBag;
use Swift\Router\Types\RouteMethodEnum;

/**
 * Class Foo
 * @package Foo\Controller
 */
#[Route(method: [RouteMethodEnum::GET, RouteMethodEnum::PATCH], route: '/foo/', name: 'foo')]
class FooController extends AbstractController {

    /**
     * Foo constructor.
     *
     * @param ConfigurationInterface $configuration
     * @param FooService $fooService
     * @param string|null $notAutowired
     */
    public function __construct(
        private ConfigurationInterface $configuration,
        private FooService $fooService,
        private string|null $notAutowired = null,
    ) {
    }

    /**
     * @param RouteParameterBag $params
     *
     * @return JSONResponse
     */
    #[Route(method: [RouteMethodEnum::GET], route: '/bar/[i:article_id]/', name: 'foo.get_bar')]
    public function getBar( RouteParameterBag $params): JsonResponse {
        // Let's return the article here

        $articleID = $params->get('article_id')->getValue();

        return new JsonResponse(array(
            'article_id' => $articleID,
            'title' => 'Foo Bar',
        ));
    }

    /**
     * @param RouteParameterBag $params
     *
     * @return JsonResponse
     */
    #[Route(method: [RouteMethodEnum::PATCH], route: '/bar/[i:article_id]/', name: 'foo.patch_bar')]
    public function patchBar( RouteParameterBag $params): JsonResponse {
        // Let's update the article here
        return new JsonResponse(array('foo bar'));
    }
}