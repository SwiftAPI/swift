<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

use Psr\Http\Message\RequestInterface;

/**
 * RequestMatcherInterface is an interface for strategies to match a Request.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface RequestMatcherInterface {

    /**
     * Decides whether the rule(s) implemented by the strategy matches the supplied request.
     *
     * @param RequestInterface $request
     *
     * @return bool true if the request matches, false otherwise
     */
    public function matches( RequestInterface $request ): bool;
}
