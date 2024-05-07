<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping;


use Cycle\Annotated\Locator\Embedding;
use Cycle\Annotated\Locator\EmbeddingLocatorInterface;
use Cycle\Annotated\Locator\EntityLocatorInterface;
use Spiral\Tokenizer\ClassesInterface;
use Swift\Code\ReflectionFactory;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\ServiceLocator;
use Swift\Orm\Attributes\Embeddable;
use Swift\Orm\DependencyInjection\OrmDiTags;

#[Autowire]
class ClassLocator implements ClassesInterface, EmbeddingLocatorInterface, EntityLocatorInterface {

    /** @var \Swift\Code\ReflectionClass[] $embeddings */
    protected array $embeddings = [];

    /** @var \Swift\Code\ReflectionClass[] $entities */
    protected array $entities = [];

    /** @var \Swift\Code\ReflectionClass[] $entities */
    protected array $classes = [];

    public function __construct(
        protected readonly ReflectionFactory $reflectionFactory,
        protected readonly ServiceLocator    $serviceLocator = new ServiceLocator(),
    ) {
        foreach ( $this->serviceLocator->getServicesByTag( OrmDiTags::ORM_EMBEDDABLE->value ) as $item ) {
            $this->embeddings[] = $this->reflectionFactory->getReflectionClass( $item );
        }

        foreach ( $this->serviceLocator->getServicesByTag( OrmDiTags::ORM_ENTITY->value ) as $item ) {
            $this->entities[] = $this->reflectionFactory->getReflectionClass( $item );
        }

        foreach ( $this->serviceLocator->getServicesByTag( OrmDiTags::ORM_ANNOTATED->value ) as $item ) {
            $this->classes[] = $this->reflectionFactory->getReflectionClass( $item );
        }
    }

    /**
     * @inheritDoc
     */
    public function getClasses( $target = null ): array {
        return $this->classes;
    }


    public function getEmbeddings(): array {
        $embeddings = [];

        foreach ( $this->entities as $reflection ) {
            $swiftEmbeddable = $this->reflectionFactory->getAttributeReader()->getClassAnnotation(
                $reflection,
                Embeddable::class,
            );

            $embeddings[] = new Embedding(
                new \Cycle\Annotated\Annotation\Embeddable(
                    role: $swiftEmbeddable->getRole(),
                    mapper: $swiftEmbeddable->getMapper(),
                    columnPrefix: $swiftEmbeddable->getColumnPrefix(),
                    columns: $swiftEmbeddable->getColumns(),
                ),
                $reflection
            );
        }

        return $embeddings;
    }

    public function getEntities(): array {
        $entities = [];

        foreach ( $this->entities as $reflection ) {
            $swiftEntity = $this->reflectionFactory->getAttributeReader()->getClassAnnotation(
                $reflection,
                \Swift\Orm\Attributes\Entity::class,
            );

            $swiftEntity->getRole();

            $entities[] = new \Cycle\Annotated\Locator\Entity(
                new \Cycle\Annotated\Annotation\Entity(
                    table: $swiftEntity->getTable(),
                ),
                $reflection
            );
        }

        return $entities;
    }
}