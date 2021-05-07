<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Resolvers;


use Closure;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\QueryPlan;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use Swift\GraphQl\ContextInterface;
use Swift\GraphQl\Directives\DirectiveInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\ServiceLocatorInterface;

/**
 * Class FieldResolver
 * @package Swift\GraphQl\Resolvers
 */
#[Autowire]
class FieldResolver {

    /**
     * FieldResolver constructor.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param ContextInterface $context
     * @param array $instances
     */
    public function __construct(
        private ServiceLocatorInterface $serviceLocator,
        private ContextInterface $context,
        private array $instances = array(),
    ) {
    }

    public function resolve( $value, $args, $context, ResolveInfo $info ) {
        $fieldName = $info->fieldName;
        $type      = $info->fieldDefinition->getType() instanceof ListOfType || $info->fieldDefinition->getType() instanceof NonNull ?
            $info->fieldDefinition->getType()->getOfType() : $info->fieldDefinition->getType();
        $property  = null;

        $this->context->setInfo($info);

        //var_dump($fieldName);

        // TODO: Implement dataloader principle
        //$queryPlan = new QueryPlan($info->parentType, $info->schema, $info->fieldNodes, $info->variableValues, $info->fragments);

        // Run directives before value generation
        $directives = $this->context->getDirectives();
        if (!empty($directives)) {
            foreach ($directives as $directive) {
                $value = $directive->execute($value, DirectiveInterface::BEFORE_VALUE);
            }
        }

        $args = $this->resolveArgs($args, $info->fieldDefinition->args);

        // If field is marked as a method on the value, execute it is as such
        if (is_object($value) && (method_exists($value, $fieldName) || method_exists($value, 'get' . ucfirst($fieldName)))) {
            $methodName = method_exists($value, $fieldName) ? $fieldName : 'get' . ucfirst($fieldName);
            $value = $this->getClassWithAutowire($value);
            return $value->{$methodName}(...$args);
        }

        // Find the field resolver (defining class and method of Query or Mutation) and executing
        $resolver = array_key_exists(key: 'declaration', array: $info->fieldDefinition->config) ?
            $this->serviceLocator->get($info->fieldDefinition->config['declaration']->declaringClass) : null;
        if ($resolver && method_exists(object_or_class: $resolver, method: $info->fieldDefinition->config['declaration']->resolve)) {
            return $resolver?->{$info->fieldDefinition->config['declaration']->resolve}(...$args);
        }

         // Pick the value
        if (is_array($value) || $value instanceof \ArrayAccess) {
            if (isset($objectValue[$fieldName])) {
                $property = $objectValue[$fieldName];
            }
        } elseif (is_object($value)) {
            if (isset($value->{$fieldName})) {
                $property = $value->{$fieldName};
            }
        }
        $value = $property instanceof Closure
            ? $property($value, $args, $context, $info)
            : $property;

        // Run after value directives
        if (!empty($directives)) {
            foreach ($directives as $directive) {
                $value = $directive->execute($value, DirectiveInterface::AFTER_VALUE);
            }
        }

        return $value;
    }

    /**
     * @param array $args
     * @param \GraphQL\Type\Definition\FieldArgument[] $fieldArgs
     *
     * @return array
     */
    private function resolveArgs( array $args, array $fieldArgs ): array {
        // Arguments will always be in array form no matter the declaration. If marked as such, instance the desired classes
        $arguments = array();
        foreach ($fieldArgs as $arg) {
            $argType = $arg->getType() instanceof NonNull ? $arg->getType()->getOfType() : $arg->getType();
            if (array_key_exists('declaration', $argType->config)) {
                $className = $argType->config['declaration']->declaringClass;
                $argValue = new $className(...$args[$arg->name]);
            } else {
                $argValue = $args[$arg->name] ?? $arg->defaultValue;
            }
            $arguments[$arg->name] = $this->parseArgValue($argValue, $argType, $arg);

            if (method_exists($arg->config['type'], 'getFields') && !empty($arg->config['type']->getFields())) {
                foreach ($arg->config['type']->getFields() as $fieldName => /** @var \GraphQL\Type\Definition\InputObjectField */ $field) {
                    $arguments[$arg->name][$fieldName] = $args[$arg->name][$field->name] ?? $field->defaultValue;
                }
            }
        }

        $this->context->setCurrentArguments(array(
            'raw' => $args,
            'parsed' => $arguments,
        ));

        return $arguments;
    }

    public function getClassWithAutowire( object|string $class ): object {
        $instance = is_object($class) ? $class : new $class();

        $reflection = $this->serviceLocator->getReflectionClass($instance::class);
        foreach ($reflection->getMethods() as $reflectionMethod) {
            $attributes = $reflectionMethod->getAttributes(Autowire::class);

            if (empty($attributes)) {
                continue;
            }

            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $autowireSetting      = !empty($reflectionParameter->getAttributes(Autowire::class)) ? $reflectionParameter->getAttributes(Autowire::class)[0]->newInstance() : new Autowire();
                $paramType            = $autowireSetting->serviceId ?? $reflectionParameter->getType()?->getName();

                // Check if the type can be resolved as a service
                if ( $this->serviceLocator->has( $paramType ) ) {
                    $instance->{$reflectionMethod->getName()}($this->serviceLocator->get($paramType));
                    continue;
                }

                // Check if the type can be resolved as a service
                if ( $this->serviceLocator->getServiceInstancesByTag( 'alias:' . $paramType . ' $' . $reflectionParameter->getName() ) ) {
                    $instance->{$reflectionMethod->getName()}($this->serviceLocator->getServiceInstancesByTag( 'alias:' . $paramType . ' $' . $reflectionParameter->getName() )[0]);
                    continue;
                }

                // Check if it has an attribute which can be resolved
                if ( $autowireSetting->tag ) {
                    $instance->{$reflectionMethod->getName()}($this->serviceLocator->getServiceInstancesByTag($autowireSetting->tag));
                    continue;
                }
            }

        }

        $this->instances[$instance::class] = true;

        return $class;
    }

    private function parseArgValue( mixed $argValue, mixed $argType, FieldArgument $argument ): mixed {
        if (($argument->name === 'id') || ($argType::class === IDType::class)) {
            return Relay::fromGlobalId($argValue)['id'];
        }

        return $argValue;
    }

}