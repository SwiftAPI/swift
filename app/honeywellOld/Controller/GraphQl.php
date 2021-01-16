<?php declare(strict_types=1);

namespace HoneywellOld\Controller;

use JetBrains\PhpStorm\Pure;
use Swift\Controller\Controller;
//use Swift\GraphQL\Annotations\Query;
use Swift\Http\Response\JSONResponse;
use Swift\Kernel\Attributes\DI;
use Swift\Router\Attributes\Route;

class GraphQl extends Controller {

    #[Pure] #[Route(type: 'GET|POST', route: '/testing123/')]
    public function test( array $params = array() ): JSONResponse {

        return new JSONResponse(['here']);
    }

//    /**
//     *
//     *
//     * @Query
//     *
//     * @return Foo
//     */
//    public function FooBar(): Foo {
//
//    }

}