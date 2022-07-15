<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Types;

use Swift\DependencyInjection\Attributes\DI;
use Swift\Kernel\KernelDiTags;
use Swift\Orm\Mapping\Definition\Field;
use Swift\Orm\Dbal\TableQuery;

/**
 * Interface TypeInterface
 * @package Swift\Orm\Types
 */
#[DI( tags: [ KernelDiTags::ENTITY_TYPE ] )]
interface TypeInterface {
    
    public function transformToPhpValue( mixed $value ): mixed;
    
    public function transformToDatabaseValue( mixed $value ): mixed;
    
    public function getName(): string;
    
    /**
     * Type the value will hold in the database
     *
     * @return string
     */
    public function getDatabaseType(): string;
    
}