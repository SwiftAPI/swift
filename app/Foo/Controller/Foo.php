<?php declare(strict_types=1);

namespace Foo\Controller;

use Foo\Service\FooService;
use Swift\Configuration\ConfigurationInterface;
use Swift\Controller\AbstractController;
use Swift\HttpFoundation\JsonResponse;
use Swift\Router\Attributes\Route;
use Swift\Router\RouteParameter;
use Swift\Router\Types\RouteMethodEnum;

/**
 * Class Foo
 * @package Foo\Controller
 */
#[Route(method: [RouteMethodEnum::GET, RouteMethodEnum::PATCH], route: '/foo/', name: 'foo')]
class Foo extends AbstractController {

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
     * @param RouteParameter[] $params
     *
     * @return JSONResponse
     */
    #[Route(method: [RouteMethodEnum::GET], route: '/bar/[i:articleid]/', name: 'foo.get_bar')]
    public function getBar( array $params): JsonResponse {
        // Let's return the article here

        $articleID = $params['articleid']->getValue();

        return new JsonResponse(array(
            'article_id' => $articleID,
            'title' => 'Foo Bar',
        ));
    }

    /**
     * @param RouteParameter[] $params
     *
     * @return JsonResponse
     */
    #[Route(method: [RouteMethodEnum::PATCH], route: '/bar/[i:articleid]/', name: 'foo.patch_bar')]
    public function patchBar( array $params): JsonResponse {
        // Let's update the article here
        return new JsonResponse(array('foo bar'));
    }
}