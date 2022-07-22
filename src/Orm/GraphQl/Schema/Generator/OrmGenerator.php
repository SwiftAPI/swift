<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\GraphQl\Schema\Generator;

use Cycle\ORM\Relation;
use Cycle\ORM\SchemaInterface;
use Doctrine\Inflector\InflectorFactory;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Swift\Dbal\Arguments\ArgumentComparison;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\Executor\Utils;
use Swift\GraphQl\Relay\Relay;
use Swift\GraphQl\Schema\Builder\Builder;
use Swift\GraphQl\Schema\Builder\ConnectionBuilder;
use Swift\GraphQl\Schema\Builder\EnumBuilder;
use Swift\GraphQl\Schema\Builder\FieldBuilder;
use Swift\GraphQl\Schema\Builder\ObjectBuilder;
use Swift\GraphQl\Schema\Generator\GeneratorInterface;
use Swift\GraphQl\Schema\Generator\ManualGeneratorInterface;
use Swift\GraphQl\Schema\NamingStrategy;
use Swift\GraphQl\Schema\Registry;
use Swift\GraphQl\Type\PhpEnumType;
use Swift\GraphQl\Type\TypeFactory;
use Swift\Orm\Factory;
use Swift\Orm\GraphQl\Executor\Resolver\OrmResolver;
use Swift\Orm\Mapping\ClassMetaDataFactory;

#[Autowire]
class OrmGenerator implements GeneratorInterface, ManualGeneratorInterface {
    
    public function __construct(
        protected readonly Factory              $ormFactory,
        protected readonly ClassMetaDataFactory $classMetaDataFactory,
        protected readonly NamingStrategy       $namingStrategy,
        protected readonly OrmResolver          $ormResolver,
    ) {
    }
    
    public function generate( \Swift\GraphQl\Schema\Registry $registry ): \Swift\GraphQl\Schema\Registry {
        $this->initCoreTypes( $registry );
        
        foreach ( $this->ormFactory->getSchema()->toArray() as $table => $schema ) {
            $class = $schema[ SchemaInterface::ENTITY ];
            $meta  = $this->classMetaDataFactory->getClassMetaData( $class );
            
            if ( ! $meta ) {
                continue;
            }
            
            $type = $this->createType( $table, $schema, $registry );
            $enum = $this->createEnum( $table, $schema, $registry );
            
            $this->addToQuery( $type, $table, $schema, $registry, $enum );
            $this->addToMutation( $type, $table, $schema, $registry );
        }
        
        foreach ( $this->ormFactory->getSchema()->toArray() as $table => $schema ) {
            $class = $schema[ SchemaInterface::ENTITY ];
            $meta  = $this->classMetaDataFactory->getClassMetaData( $class );
            
            if ( ! $meta ) {
                continue;
            }
            
            $relations = $schema[ SchemaInterface::RELATIONS ];
            
            if ( empty( $relations ) ) {
                continue;
            }
            
            foreach ( $relations as $relation => $relationSchema ) {
                $currentType = $registry->getType( Registry::$alias[ $table ] );
                $targetType  = $registry->getType( Registry::$alias[ $relationSchema[ SchemaInterface::ENTITY ] ] ?? '' );
                
                if ( ! $targetType ) {
                    continue;
                }
                
                $currentType->addField(
                    $relation,
                    FieldBuilder::create(
                        $relation,
                        static function () use ( $targetType, $relationSchema ) {
                            $type      = Registry::$typeMap[ $targetType->getName() ];
                            $subSchema = $relationSchema[ Relation::SCHEMA ];
                            
                            if ( in_array( $relationSchema[ Relation::TYPE ], [ Relation::HAS_MANY, Relation::MANY_TO_MANY ], true ) ) {
                                $type = Builder::listOf( $type );
                            }
                            if ( ! $subSchema[ Relation::NULLABLE ] ) {
                                $type = Builder::nonNull( $type );
                            }
                            
                            return $type;
                        }
                    )->buildType(),
                );
            }
        }
        
        return $registry;
    }
    
    protected function initCoreTypes( Registry $registry ): void {
        $this->resolveEnum( $registry, ArgumentComparison::class );
    }
    
    protected function createType( string $table, array $schema, Registry $registry ): ObjectBuilder {
        $inflector = InflectorFactory::create()->build();
        $class     = $schema[ SchemaInterface::ENTITY ];
        $role      = $schema[ SchemaInterface::ROLE ] ?? $table;
        $meta      = $this->classMetaDataFactory->getClassMetaData( $class );
        
        $type = Builder::objectType( ucfirst( $inflector->camelize( $inflector->singularize( $table ) ) ) )
                       ->addInterface( Relay::NODE, static fn() => Registry::$typeMap[ Relay::NODE ] )
                       ->setFieldResolver( function ( $objectValue, array $args, $context, ResolveInfo $info ) {
                           if ( ! $objectValue && ! empty( $args[ 'id' ] ) ) {
                               return $this->ormResolver->resolveSingle( $objectValue, $args, $context, $info );
                           }
            
                           return $objectValue;
                       } );
        
        foreach ( $meta?->getEntity()->getFields() ?? [] as $field ) {
            if ( $field->getEnum() ) {
                $this->resolveEnum( $registry, $field->getEnum() );
            }
            
            $type->addField(
                $field->getPropertyName(),
                Builder::fieldType(
                    $field->getPropertyName(),
                    function () use ( $field, $meta ) {
                        $type = $this->resolveType( $field->getType()->getName() );
                        if ( $field === $meta->getEntity()->getPrimaryKey() ) {
                            $type = Type::id();
                        }
                        if ( $field->getEnum() ) {
                            $type = Registry::$typeMap[ PhpEnumType::baseName( $field->getEnum() ) ];
                        }
                        if ( ! $field->isNullable() ) {
                            $type = Builder::nonNull( $type );
                        }
                        
                        return $type;
                    }
                )->buildType(),
            );
        }
        
        $registry->objectType( $type );
        
        Registry::$alias[ $role ] = $type->getName();
        
        return $type;
    }
    
    protected function createEnum( string $table, array $schema, Registry $registry ): EnumBuilder {
        $inflector = InflectorFactory::create()->build();
        $class     = $schema[ SchemaInterface::ENTITY ];
        $meta      = $this->classMetaDataFactory->getClassMetaData( $class );
        
        $type = Builder::enumType( ucfirst( $inflector->camelize( $table ) ) . 'Fields' );
        
        $ref = $this;
        
        foreach ( $meta?->getEntity()->getFields() ?? [] as $field ) {
            if ( $field->getEnum() ) {
                $ref->resolveEnum( $registry, $field->getEnum() );
            }
            
            $type->addValue(
                $inflector->capitalize( $field->getPropertyName() ),
            );
        }
        
        $registry->enumType( $type );
        
        return $type;
    }
    
    protected function addToQuery( ObjectBuilder $type, string $table, array $schema, Registry $registry, EnumBuilder $enum ): void {
        $ref = $this;
        
        $registry->extendType( 'Query', function ( ObjectBuilder $object ) use ( $ref, $enum, $registry, $schema, $table, $type ) {
            $object->addField(
                $this->namingStrategy->singleQueryName( $table ),
                Builder::fieldType(
                    $this->namingStrategy->singleQueryName( $table ),
                    static function () use ( $type ) {
                        return Registry::$typeMap[ $type->getName() ];
                    },
                )->addArgument(
                    'id',
                    Builder::nonNull( Type::id() ),
                )->setResolver( static function ( $objectValue, $args, $context, ResolveInfo $info ) use ( $ref ) {
                    return $ref->ormResolver->resolveSingle( $objectValue, $args, $context, $info );
                } )->buildType(),
            );
            
            Registry::$alias[ $this->namingStrategy->singleQueryName( $table ) ] = $schema[ SchemaInterface::ENTITY ];
            
            $this->buildListQuery( $object, $table, $schema, $registry, $type, $enum );
            
            return $object;
        } );
    }
    
    protected function addToMutation( ObjectBuilder $type, string $table, array $schema, Registry $registry ): void {
        $inflector = InflectorFactory::create()->build();
        
        $registry->extendType( 'Mutation', function ( ObjectBuilder $object ) use ( $registry, $schema, $inflector, $table, $type ) {
            $this->buildMutationCreate( $object, $table, $schema, $registry, $type );
            $this->buildMutationUpdate( $object, $table, $schema, $registry, $type );
            
            $object->addField(
                ucfirst( $inflector->singularize( $inflector->camelize( $table ) ) ) . 'Delete',
                Builder::fieldType(
                    ucfirst( $inflector->singularize( $inflector->camelize( $table ) ) ) . 'Delete',
                    static function () use ( $type ) {
                        return Registry::$typeMap[ $type->getName() ];
                    },
                )->addArgument(
                    'id',
                    Builder::nonNull( Type::id() ),
                )->buildType(),
            );
            
            return $object;
        } );
    }
    
    protected function resolveType( string $type ): \GraphQL\Type\Definition\ScalarType|\GraphQL\Type\Definition\ListOfType {
        return match ( $type ) {
            'string', 'text', 'longtext', 'enum', 'ENUM' => Type::string(),
            'int', 'time' => Type::int(),
            'float', 'big_float' => Type::float(),
            'bool' => Type::boolean(),
            'array' => Type::listOf( Type::string() ),
            'datetime' => TypeFactory::dateTime(),
            'uuid', 'UUID' => TypeFactory::uuid(),
            'json', 'JSON', 'object' => TypeFactory::json(),
        };
    }
    
    protected function resolveEnum( Registry $registry, string $type ): EnumBuilder {
        $name = PhpEnumType::baseName( $type );
        
        if ( $registry->getType( $name ) ) {
            return $registry->getType( $name );
        }
        
        $enum = EnumBuilder::create( $type );
        
        $registry->enumType( $enum );
        
        return $enum;
    }
    
    public function buildListQuery( ObjectBuilder $object, string $table, array $schema, Registry $registry, ObjectBuilder $type, EnumBuilder $enum ): void {
        $name       = $this->namingStrategy->listQueryName( $table );
        $ref        = $this;
        $connection = $this->buildConnection( $object, $table, $schema, $registry, $type, $enum );
        
        $field                                                             = Builder::fieldType(
            $name,
            static function () use ( $connection ) {
                return Registry::$typeMap[ $connection->getName() ];
            },
        )->setResolver( static function ( $objectValue, $args, $context, ResolveInfo $info ) use ( $ref ) {
            return $ref->ormResolver->resolveList( $objectValue, $args, $context, $info );
        } );
        Registry::$alias[ $this->namingStrategy->listQueryName( $table ) ] = $schema[ SchemaInterface::ENTITY ];
        
        $field
            ->addArgument(
                'first',
                static function () {
                    return Type::int();
                },
                'The number of items to return after the referenced "after" cursor',
            )
            ->addArgument(
                'last',
                static function () {
                    return Type::int();
                },
                'The number of items to return before the referenced "before" cursor',
            )
            ->addArgument(
                'before',
                static function () {
                    return Type::string();
                },
                'Cursor used along with the "first" argument to reference where in the dataset to get data',
            )
            ->addArgument(
                'after',
                static function () {
                    return Type::string();
                },
                'Cursor used along with the "last" argument to reference where in the dataset to get data',
            );
        
        $whereType = Builder::inputObject( $this->namingStrategy->listQueryWhereArgs( $table ) )
                            ->addField(
                                'orderBy',
                                static function () use ( $enum ) {
                                    return Registry::$typeMap[ $enum->getName() ];
                                },
                            );
        
        $class = $schema[ SchemaInterface::ENTITY ];
        $meta  = $this->classMetaDataFactory->getClassMetaData( $class );
        $ref   = $this;
        
        foreach ( $meta?->getEntity()?->getFields() ?? [] as $fieldItem ) {
            $inputItem = Builder::inputObject( $this->namingStrategy->inputWhereArgsFieldName( $fieldItem->getPropertyName(), $name ) )
                                ->addField(
                                    'compare',
                                    static function () {
                                        return Registry::$typeMap[ PhpEnumType::baseName( ArgumentComparison::class ) ];
                                    },
                                )
                                ->addField(
                                    'value',
                                    static function () use ( $meta, $ref, $fieldItem ) {
                                        $type = $ref->resolveType( $fieldItem->getType()->getName() );
                                        if ( $fieldItem === $meta->getEntity()->getPrimaryKey() ) {
                                            $type = Type::id();
                                        }
                                        if ( $fieldItem->getEnum() ) {
                                            $type = Registry::$typeMap[ PhpEnumType::baseName( $fieldItem->getEnum() ) ];
                                        }
                    
                                        return $type;
                                    },
                                )
                                ->addField(
                                    'values',
                                    static function () use ( $meta, $ref, $fieldItem ) {
                                        $type = $ref->resolveType( $fieldItem->getType()->getName() );
                                        if ( $fieldItem === $meta->getEntity()->getPrimaryKey() ) {
                                            $type = Type::id();
                                        }
                                        if ( $fieldItem->getEnum() ) {
                                            $type = Registry::$typeMap[ PhpEnumType::baseName( $fieldItem->getEnum() ) ];
                                        }
                    
                                        return Builder::listOf( $type );
                                    },
                                );
            
            $registry->inputObjectType( $inputItem );
            
            $whereType->addField(
                $fieldItem->getPropertyName(),
                static fn() => Registry::$typeMap[ $inputItem->getName() ],
            );
        }
        
        $field->addArgument(
            'where',
            static fn() => Builder::listOf( Registry::$typeMap[ $whereType->getName() ] ),
            'Filters to apply to the query',
        );
        
        
        $registry->inputObjectType( $whereType );
        
        $object->addField(
            $name,
            $field->buildType(),
        );
    }
    
    protected function buildConnection( ObjectBuilder $object, string $table, array $schema, Registry $registry, ObjectBuilder $type, EnumBuilder $enum ): ConnectionBuilder {
        $name = $this->namingStrategy->connectionName( $table );
        
        $conn = Builder::connectionType(
            $name,
            $type,
        );
        
        $registry->objectType( $conn );
        
        return $conn;
    }
    
    protected function buildMutationUpdate( ObjectBuilder $object, string $table, array $schema, Registry $registry, ObjectBuilder $type ): void {
        $name = $this->namingStrategy->getMutationUpdateName( $table );
        $ref  = $this;
        
        $field                    = Builder::fieldType(
            $name,
            static function () use ( $type ) {
                return Registry::$typeMap[ $type->getName() ];
            },
        )->addArgument(
            'id',
            Builder::nonNull( Type::id() ),
        )->setResolver( static function ( $objectValue, $args, $context, ResolveInfo $info ) use ( $ref ) {
            return $ref->ormResolver->resolveUpdate( $objectValue, $args, $context, $info );
        } );
        Registry::$alias[ $name ] = $schema[ SchemaInterface::ENTITY ];
        
        
        $class = $schema[ SchemaInterface::ENTITY ];
        $meta  = $this->classMetaDataFactory->getClassMetaData( $class );
        $ref   = $this;
        
        $fieldsToIgnore = Utils::extractAutomaticFieldsFromSchema( $schema );
        
        $inputItem = Builder::inputObject( $this->namingStrategy->getMutationUpdateInputName( $table ) );
        foreach ( $meta?->getEntity()?->getFields() ?? [] as $fieldItem ) {
            if ( $fieldItem === $meta?->getEntity()->getPrimaryKey() ) {
                continue;
            }
            if ( in_array( $fieldItem->getPropertyName(), $fieldsToIgnore, true ) ) {
                continue;
            }
            $inputItem
                ->addField(
                    $fieldItem->getPropertyName(),
                    static function () use ( $meta, $ref, $fieldItem ) {
                        $type = $ref->resolveType( $fieldItem->getType()->getName() );
                        
                        if ( $fieldItem->getEnum() ) {
                            $type = Registry::$typeMap[ PhpEnumType::baseName( $fieldItem->getEnum() ) ];
                        }
                        
                        return $type;
                    },
                );
        }
        
        $registry->inputObjectType( $inputItem );
        $field->addArgument(
            'input',
            static fn() => Registry::$typeMap[ $inputItem->getName() ],
        );
        
        $object->addField(
            $this->namingStrategy->getMutationUpdateName( $table ),
            $field->buildType(),
        );
    }
    
    protected function buildMutationCreate( ObjectBuilder $object, string $table, array $schema, Registry $registry, ObjectBuilder $type ): void {
        $name = $this->namingStrategy->getMutationCreateName( $table );
        $ref  = $this;
        
        $field                    = Builder::fieldType(
            $name,
            static function () use ( $type ) {
                return Registry::$typeMap[ $type->getName() ];
            },
        )->setResolver( static function ( $objectValue, $args, $context, ResolveInfo $info ) use ( $ref ) {
            return $ref->ormResolver->resolveCreate( $objectValue, $args, $context, $info );
        } );
        Registry::$alias[ $name ] = $schema[ SchemaInterface::ENTITY ];
        
        
        $class = $schema[ SchemaInterface::ENTITY ];
        $meta  = $this->classMetaDataFactory->getClassMetaData( $class );
        $ref   = $this;
        
        $fieldsToIgnore = Utils::extractAutomaticFieldsFromSchema( $schema );
        
        $inputItem = Builder::inputObject( $this->namingStrategy->getMutationCreateInputName( $table ) );
        foreach ( $meta?->getEntity()?->getFields() ?? [] as $fieldItem ) {
            if ( $fieldItem === $meta?->getEntity()->getPrimaryKey() ) {
                continue;
            }
            if ( in_array( $fieldItem->getPropertyName(), $fieldsToIgnore, true ) ) {
                continue;
            }
            $inputItem
                ->addField(
                    $fieldItem->getPropertyName(),
                    static function () use ( $meta, $ref, $fieldItem ) {
                        $type = $ref->resolveType( $fieldItem->getType()->getName() );
                        
                        if ( $fieldItem->getEnum() ) {
                            $type = Registry::$typeMap[ PhpEnumType::baseName( $fieldItem->getEnum() ) ];
                        }
                        if ( ! $fieldItem->isNullable() ) {
                            $type = Builder::nonNull( $type );
                        }
                        
                        return $type;
                    },
                );
        }
        
        $registry->inputObjectType( $inputItem );
        $field->addArgument(
            'input',
            static fn() => Registry::$typeMap[ $inputItem->getName() ],
        );
        
        $object->addField(
            $this->namingStrategy->getMutationCreateName( $table ),
            $field->buildType(),
        );
    }
    
}