<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Mapping;


use Swift\Kernel\TypeSystem\Enum;

/**
 * Class IndexTypes
 * @package Swift\Model\Mapping
 */
class IndexType extends Enum {

    public const PRIMARY = 'PRIMARY';
    public const INDEX = 'INDEX';
    public const UNIQUE = 'UNIQUE';

}