<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping;


use Spiral\Tokenizer\ClassesInterface;
use Swift\Code\ReflectionFactory;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\ServiceLocator;
use Swift\Orm\DependencyInjection\OrmDiTags;

#[Autowire]
class ClassLocator implements ClassesInterface {
    
    /** @var \Swift\Code\ReflectionClass[] */
    protected array $entities = [];
    
    public function __construct(
        protected readonly ReflectionFactory $reflectionFactory,
        protected readonly ServiceLocator $serviceLocator = new ServiceLocator(),
    ) {
        foreach( $this->serviceLocator->getServicesByTag( OrmDiTags::ORM_ANNOTATED->value ) as $item ) {
            $this->entities[] = $this->reflectionFactory->getReflectionClass( $item );
        }
    }
    
    /**
     * @inheritDoc
     */
    public function getClasses( $target = null ): array {
        return $this->entities;
    }
    
    
}