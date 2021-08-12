<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Types;


use Swift\Model\Mapping\Field;
use Swift\Model\Query\TableQuery;

class LongText implements TypeInterface {

    public const LONGTEXT = 'longtext';

    /**
     * @inheritDoc
     */
    public function getSqlDeclaration( Field $field, TableQuery $query ): string {
        return 'longtext';
    }

    public function transformToPhpValue( mixed $value ): string {
        return (string) $value;
    }

    public function transformToDatabaseValue( mixed $value ): string {
        return (string) $value;
    }

    public function getName(): string {
        return static::LONGTEXT;
    }

}