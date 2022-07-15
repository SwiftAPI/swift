<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Entity;

use Cycle\ORM\EntityProxyInterface;
use Cycle\ORM\Mapper\Proxy\EntityProxyTrait;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Kernel\KernelDiTags;
use Swift\Orm\Dbal\EntityResultInterface;
use Swift\Orm\Dbal\ResultTrait;

/**
 * Class Entity
 * @package Swift\Orm\Entity
 */
#[DI( tags: [ KernelDiTags::ENTITY ], autowire: false )]
abstract class AbstractEntity extends \stdClass implements EntityInterface, EntityResultInterface, EntityProxyInterface {
    
    use ResultTrait;
    
    
}