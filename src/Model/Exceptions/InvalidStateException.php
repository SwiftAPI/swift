<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Exceptions;

use Swift\Kernel\Attributes\DI;

/**
 * Class InvalidStateException
 * @package Swift\Model\Exceptions
 */
#[DI(exclude: true)]
class InvalidStateException extends \InvalidArgumentException {

}