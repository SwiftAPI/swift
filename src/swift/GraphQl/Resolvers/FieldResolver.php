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
use GraphQL\Type\Definition\ResolveInfo;
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
     */
    public function __construct(
        private ServiceLocatorInterface $serviceLocator,
    ) {
    }

    public function resolve( $value, $args, $context, ResolveInfo $info ) {
        $fieldName = $info->fieldName;
        $property  = null;


        //var_dump($value);
        //var_dump($args);
        //var_dump($context);
        //var_dump($info->fieldDefinition->config);
        //var_dump(array_keys($info->fieldDefinition->config));

        $resolver = array_key_exists(key: 'declaration', array: $info->fieldDefinition->config) ?
            $this->serviceLocator->get($info->fieldDefinition->config['declaration']->declaringClass) : null;

        if ($resolver && method_exists(object_or_class: $resolver, method: $info->fieldDefinition->config['declaration']->resolve)) {
            return $resolver?->{$info->fieldDefinition->config['declaration']->resolve}(...$args);
        }

        if (is_array($value) || $value instanceof \ArrayAccess) {
            if (isset($objectValue[$fieldName])) {
                $property = $objectValue[$fieldName];
            }
        } elseif (is_object($value)) {
            if (isset($value->{$fieldName})) {
                $property = $value->{$fieldName};
            }
        }

        return $property instanceof Closure
            ? $property($value, $args, $context, $info)
            : $property;
    }

}