<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl;


use Swift\Kernel\TypeSystem\Enum;

/**
 * Class ObjectTypes
 * @package Swift\GraphQl
 */
class ObjectTypes extends Enum {

    public const INPUT_TYPE = 'inputtypes';
    public const OUTPUT_TYPE = 'types';
    public const MUTATION = 'mutations';
    public const ENUM = 'enums';
    public const INTERFACE = 'interfaces';
    public const EXTENSION = 'extensions';
    public const QUERY = 'queries';

}