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

/**
 * Class BigFloat
 * @package Swift\Model\Types
 */
class BigFloat implements TypeInterface {

    public const BIG_FLOAT = 'big_float';

    /**
     * @inheritDoc
     */
    public function getSqlDeclaration( Field $field, TableQuery $query ): string {
        return sprintf('varchar(%s)', $field->getLength() ?? 128);
    }

    public function transformToPhpValue( mixed $value ): float {
        return (float) $value;
    }

    public function transformToDatabaseValue( mixed $value ): string {
        return (string) $value;
    }

    public function getName(): string {
        return self::BIG_FLOAT;
    }


}