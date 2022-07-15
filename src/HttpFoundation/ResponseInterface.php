<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

/**
 * Interface ResponseInterface
 * @package Swift\HttpFoundation
 */
interface ResponseInterface extends \Psr\Http\Message\ResponseInterface {

    /**
     * Send output to browser
     *
     * @return static
     */
    public function send(): static;

}