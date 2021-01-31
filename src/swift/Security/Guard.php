<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security;

use Psr\Http\Message\RequestInterface;
use Swift\Configuration\Configuration;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class Guard
 * @package Swift\Security
 */
#[Autowire]
class Guard implements GuardInterface {

    /**
     * Guard constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(
        private Configuration $configuration,
    ) {
    }

    public function guard( RequestInterface $request ): void {
        var_dump($request->getUri());

    }


}