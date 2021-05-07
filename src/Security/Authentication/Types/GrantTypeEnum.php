<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Types;

use Swift\Kernel\TypeSystem\Enum;

/**
 * Class GrantTypeEnum
 * @package Swift\Security\Authentication\Types
 */
class GrantTypeEnum extends Enum {

    public const CLIENT_CREDENTIALS = 'CLIENT_CREDENTIALS';
    public const REFRESH_TOKEN = 'REFRESH_TOKEN';

}