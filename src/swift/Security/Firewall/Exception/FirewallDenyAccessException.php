<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall\Exception;


use Swift\Security\Authentication\Exception\AuthenticationException;

/**
 * Class FirewallDenyAccessException
 * @package Swift\Security\Firewall\Exception
 */
class FirewallDenyAccessException extends AuthenticationException {

}