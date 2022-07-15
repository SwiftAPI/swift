<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;

use JetBrains\PhpStorm\Deprecated;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\HttpFoundation\ServerRequest;

/**
 * Class HTTPRequest
 * @package Swift\Router
 */
#[Deprecated(replacement: ServerRequest::class), Autowire]
class HTTPRequest {

	/**
	 * @var Request $request
	 */
	public Request $request;

    /**
     * HTTPRequest constructor.
     *
     * @param Request $request
     */
	public function __construct(
			Request $request
	) {
		$this->request      = $request;
	}


}