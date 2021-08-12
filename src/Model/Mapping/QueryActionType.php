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
 * MySql Query Action Type which can be performed on columns and indexes
 */
class QueryActionType extends Enum {

    public const ADD = 'ADD';
    public const MODIFY = 'MODIFY';
    public const DROP = 'DROP';

}