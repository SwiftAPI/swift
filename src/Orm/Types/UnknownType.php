<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Types;


final class UnknownType implements TypeInterface {

    public function __construct(
        private readonly string $name = 'unknown',
    ) {
    }

    public function transformToPhpValue( mixed $value ): mixed {
        return $value;
    }

    public function transformToDatabaseValue( mixed $value ): mixed {
        return $value;
    }

    public function getName(): string {
        return $this->name;
    }
    
    public function getDatabaseType( \Swift\Orm\Mapping\Definition\Field $field ): string {
        return 'text';
    }
    
}