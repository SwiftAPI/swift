<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\GraphQl\Executor\Resolver;


use GraphQL\Exception\InvalidArgument;
use GraphQL\Type\Definition\ResolveInfo;
use Swift\Code\PropertyReader;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\Executor\Resolver\AbstractResolver;
use Swift\GraphQl\Executor\Utils;
use Swift\GraphQl\Schema\Registry;
use Swift\Orm\EntityManagerInterface;

#[Autowire]
class OrmResolver extends AbstractResolver {
    
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly PropertyReader         $propertyReader,
    ) {
    }
    
    public function resolveSingle( $objectValue, $args, $context, ResolveInfo $info ): mixed {
        $entityName = Registry::$alias[ $info->fieldDefinition->getName() ] ?? throw new InvalidArgument( 'No entity name found for field ' . $info->fieldDefinition->getName() );
        
        return $this->entityManager->findByPk( $entityName, (int) $args[ 'id' ] ) ?? throw new InvalidArgument( 'No entity found for id ' . $args[ 'id' ] );
    }
    
    public function resolveList( $objectValue, $args, $context, ResolveInfo $info ): mixed {
        $entityName = Registry::$alias[ $info->fieldDefinition->getName() ] ?? throw new InvalidArgument( 'No entity name found for field ' . $info->fieldDefinition->getName() );
    
        return $this->entityManager->findMany( $entityName, [], Utils::whereArgsToOrmArgument( $args ) ) ?? throw new InvalidArgument( 'No entities found' );
    }
    
    public function resolveUpdate( $objectValue, $args, $context, ResolveInfo $info ): mixed {
        $entityName = Registry::$alias[ $info->fieldDefinition->getName() ] ?? throw new InvalidArgument( 'No entity name found for field ' . $info->fieldDefinition->getName() );
        $entity     = $this->entityManager->findByPk( $entityName, (int) $args[ 'id' ] ) ?? throw new InvalidArgument( 'No entity found for id ' . $args[ 'id' ] );
        
        foreach ( $args[ 'input' ] as $key => $value ) {
            $this->propertyReader->setPropertyValue( $entity, $key, $value );
        }
        
        $this->entityManager->persist( $entity );
        $this->entityManager->run();
        
        return $entity;
    }
    
    public function resolveCreate( $objectValue, $args, $context, ResolveInfo $info ): mixed {
        $entityName = Registry::$alias[ $info->fieldDefinition->getName() ] ?? throw new InvalidArgument( 'No entity name found for field ' . $info->fieldDefinition->getName() );
        $entity     = new $entityName();
        
        foreach ( $args[ 'input' ] as $key => $value ) {
            $this->propertyReader->setPropertyValue( $entity, $key, $value );
        }
        
        $this->entityManager->persist( $entity );
        $this->entityManager->run();
        
        return $entity;
    }
    
}