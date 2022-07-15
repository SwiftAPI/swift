<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\SchemaInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\Collection\CollectionFactory;
use Swift\Orm\Types\Typecast;

#[Autowire]
class Factory {
    
    protected ORMInterface $orm;
    protected SchemaInterface $schema;
    protected \Swift\Orm\Behavior\EventDrivenCommandGenerator $commandGenerator;
    protected \Swift\DependencyInjection\ContainerInterface $container;
    
    public function __construct(
        protected \Swift\Dbal\Dbal     $dbal,
        protected \Swift\Orm\Schema\Factory $schemaFactory,
    ) {
    }
    
    /**
     * @return \Cycle\ORM\ORMInterface
     */
    public function getOrm(): ORMInterface {
        if ( ! isset( $this->orm ) ) {
            $schema    = $this->getSchema();
            $this->orm = new \Swift\Orm\ORM(
                ( new \Swift\Orm\CoreFactory(
                                              $this->dbal,
                    defaultCollectionFactory: new CollectionFactory(),
                ) )->withDefaultSchemaClasses(
                    [
                        SchemaInterface::TYPECAST_HANDLER => [
                            Typecast::class,
                            \Cycle\ORM\Parser\Typecast::class,
                        ],
                    ],
                ),
                $schema,
                $this->getCommandGenerator(),
            );
        }
        
        return $this->orm;
    }
    
    /**
     * @return \Cycle\ORM\SchemaInterface
     */
    public function getSchema(): SchemaInterface {
        if ( ! isset( $this->schema ) ) {
            $this->schema = $this->schemaFactory->createSchema();
        }
        
        return $this->schema;
    }
    
    public function getCommandGenerator(): \Swift\Orm\Behavior\EventDrivenCommandGenerator {
        if ( ! isset( $this->commandGenerator ) ) {
            $this->commandGenerator = new \Swift\Orm\Behavior\EventDrivenCommandGenerator( $this->getSchema(), $this->container );
        }
        
        return $this->commandGenerator;
    }
    
    public function getSchemaFactory(): \Swift\Orm\Schema\Factory {
        return $this->schemaFactory;
    }
    
    #[Autowire]
    public function setServiceContainer( #[Autowire( serviceId: 'service_container' )] \Swift\DependencyInjection\ContainerInterface $container ): void {
        $this->container = $container;
    }
    
}