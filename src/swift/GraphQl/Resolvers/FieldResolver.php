<?php declare(strict_types=1);


namespace Swift\GraphQl\Resolvers;


use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Swift\Kernel\ContainerService\ContainerService;

class FieldResolver {


    /**
     * FieldResolver constructor.
     *
     * @param ContainerService|null $container
     */
    public function __construct(
        private ?ContainerService $container = null,
    ) {
        global $containerBuilder;

        $this->container = $containerBuilder;
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
            $this->container->get($info->fieldDefinition->config['declaration']->declaringClass) : null;

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