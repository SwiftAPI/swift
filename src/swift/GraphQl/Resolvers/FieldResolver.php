<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Resolvers;


use Closure;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\QueryPlan;
use GraphQL\Type\Definition\ResolveInfo;
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
     */
    public function __construct(
        private ServiceLocatorInterface $serviceLocator,
        private ContextInterface $context,
    ) {
    }

    public function resolve( $value, $args, $context, ResolveInfo $info ) {
        $fieldName = $info->fieldName;
        $type      = $info->fieldDefinition->getType() instanceof ListOfType || $info->fieldDefinition->getType() instanceof NonNull ?
            $info->fieldDefinition->getType()->getOfType() : $info->fieldDefinition->getType();
        $property  = null;

        $this->context->setInfo($info);

        // TODO: Implement dataloader principle
        //$queryPlan = new QueryPlan($info->parentType, $info->schema, $info->fieldNodes, $info->variableValues, $info->fragments);

        // Run directives before value generation
        $directives = $this->context->getDirectives();
        if (!empty($directives)) {
            foreach ($directives as $directive) {
                $value = $directive->execute($value, DirectiveInterface::BEFORE_VALUE);
            }
        }

        // If field is marked as a method on the value, execute it is as such
        if (is_object($value) && (method_exists($value, $fieldName) || method_exists($value, 'get' . ucfirst($fieldName)))) {
            $methodName = method_exists($value, $fieldName) ? $fieldName : 'get' . ucfirst($fieldName);
            return $value->{$methodName}();
        }

        // Arguments will always be in array form no matter the declaration. If marked as such, instance the desired classes
        $arguments = array();
        if (!empty($info->fieldDefinition->args)) {
            foreach ($info->fieldDefinition->args as $arg) {
                $argType = $arg->getType() instanceof NonNull ? $arg->getType()->getOfType() : $arg->getType();
                if (array_key_exists('declaration', $argType->config)) {
                    $className = $argType->config['declaration']->declaringClass;
                    $argValue = new $className(...$args[$arg->name]);
                } else {
                    $argValue = $args[$arg->name];
                }
                $arguments[$arg->name] = $argValue;
            }
            $args = $arguments;
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

}