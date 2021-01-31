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

/**
 * Interface GuardInterface
 * @package Swift\Security
 */
interface GuardInterface {

    /**
     * Guard given request
     *
     * @param RequestInterface $request
     */
    public function guard( RequestInterface $request ): void;

}