<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use Swift\Kernel\Attributes\DI;

/**
 * Class NullToken
 * @package Swift\Security\Authentication\Token
 */
#[DI(autowire: false)]
class NullToken extends AbstractToken {

}