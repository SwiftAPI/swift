<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Types;


use Swift\Kernel\TypeSystem\Enum;

/**
 * Class Serialize
 * @package Swift\Model\Types
 */
class Serialize extends Enum {

    public const DATETIME = 'datetime';
    public const JSON = 'json';
    public const BOOL = 'bool';

}