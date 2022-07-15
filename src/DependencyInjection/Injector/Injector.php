<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection\Injector;

use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Yiisoft\Injector\InvalidArgumentException;
use Yiisoft\Injector\MissingInternalArgumentException;
use Yiisoft\Injector\MissingRequiredArgumentException;

/**
 * Injector is able to analyze callable dependencies based on type hinting and
 * inject them from any PSR-11 compatible container.
 */
final class Injector {
    private ContainerInterface $container;
    
    public function __construct( ContainerInterface $container ) {
        $this->container = $container;
    }
    
    /**
     * Invoke a callback with resolving dependencies based on parameter types.
     *
     * This methods allows invoking a callback and let type hinted parameter names to be
     * resolved as objects of the Container. It additionally allow calling function passing named arguments.
     *
     * For example, the following callback may be invoked using the Container to resolve the formatter dependency:
     *
     * ```php
     * $formatString = function($string, \Yiisoft\I18n\MessageFormatterInterface $formatter) {
     *    // ...
     * }
     *
     * $injector = new Yiisoft\Injector\Injector($container);
     * $injector->invoke($formatString, ['string' => 'Hello World!']);
     * ```
     *
     * This will pass the string `'Hello World!'` as the first argument, and a formatter instance created
     * by the DI container as the second argument.
     *
     * @param callable $callable  callable to be invoked.
     * @param array    $arguments The array of the function arguments.
     *                            This can be either a list of arguments, or an associative array where keys are argument names.
     *
     * @return mixed the callable return value.
     * @throws ContainerExceptionInterface if a dependency cannot be resolved or if a dependency cannot be fulfilled.
     * @throws ReflectionException
     *
     * @throws MissingRequiredArgumentException if required argument is missing.
     */
    public function invoke( callable $callable, array $arguments = [] ) {
        $callable   = Closure::fromCallable( $callable );
        $reflection = new ReflectionFunction( $callable );
        
        return $reflection->invokeArgs( $this->resolveDependencies( $reflection, $arguments ) );
    }
    
    /**
     * Creates an object of a given class with resolving constructor dependencies based on parameter types.
     *
     * This methods allows invoking a constructor and let type hinted parameter names to be
     * resolved as objects of the Container. It additionally allow calling constructor passing named arguments.
     *
     * For example, the following constructor may be invoked using the Container to resolve the formatter dependency:
     *
     * ```php
     * class StringFormatter
     * {
     *     public function __construct($string, \Yiisoft\I18n\MessageFormatterInterface $formatter)
     *     {
     *         // ...
     *     }
     * }
     *
     * $injector = new Yiisoft\Injector\Injector($container);
     * $stringFormatter = $injector->make(StringFormatter::class, ['string' => 'Hello World!']);
     * ```
     *
     * This will pass the string `'Hello World!'` as the first argument, and a formatter instance created
     * by the DI container as the second argument.
     *
     * @param string                $class     name of the class to be created.
     * @param array                 $arguments The array of the function arguments.
     *                                         This can be either a list of arguments, or an associative array where keys are argument names.
     *
     * @return object The object of the given class.
     *
     * @psalm-suppress MixedMethodCall
     *
     * @psalm-template T
     * @psalm-param class-string<T> $class
     * @psalm-return T
     * @throws InvalidArgumentException|MissingRequiredArgumentException
     * @throws ReflectionException
     *
     * @throws ContainerExceptionInterface
     */
    public function make( string $class, array $arguments = [] ): object {
        $classReflection = new ReflectionClass( $class );
        if ( ! $classReflection->isInstantiable() ) {
            throw new \InvalidArgumentException( "Class $class is not instantiable." );
        }
        $reflection = $classReflection->getConstructor();
        if ( $reflection === null ) {
            // Method __construct() does not exist
            return new $class();
        }
        
        return new $class( ...$this->resolveDependencies( $reflection, $arguments ) );
    }
    
    /**
     * Resolve dependencies for the given function reflection object and a list of concrete arguments
     * and return array of arguments to call the function with.
     *
     * @param ReflectionFunctionAbstract $reflection function reflection.
     * @param array                      $arguments  concrete arguments.
     *
     * @return array resolved arguments.
     * @throws InvalidArgumentException|MissingRequiredArgumentException
     * @throws ReflectionException
     *
     * @throws ContainerExceptionInterface
     */
    private function resolveDependencies( ReflectionFunctionAbstract $reflection, array $arguments = [] ): array {
        $state = new ResolvingState( $reflection, $arguments );
        
        $isInternalOptional = false;
        $internalParameter  = '';
        
        foreach ( $reflection->getParameters() as $parameter ) {
            if ( $isInternalOptional ) {
                // Check custom parameter definition for an internal function
                if ( $state->hasNamedArgument( $parameter->getName() ) ) {
                    throw new MissingInternalArgumentException( $reflection, $internalParameter );
                }
                continue;
            }
            // Resolve parameter
            $resolved = $this->resolveParameter( $parameter, $state );
            if ( $resolved === true ) {
                continue;
            }
            
            if ( $resolved === false ) {
                throw new MissingRequiredArgumentException( $reflection, $parameter->getName() );
            }
            // Internal function. Parameter not resolved
            $isInternalOptional = true;
            $internalParameter  = $parameter->getName();
        }
        
        return $state->getResolvedValues();
    }
    
    /**
     * @return bool|null True if argument resolved; False if not resolved; Null if parameter is optional but without
     * default value in a Reflection object. This is possible for internal functions.
     * @throws ReflectionException
     *
     * @throws NotFoundExceptionInterface
     */
    private function resolveParameter( ReflectionParameter $parameter, ResolvingState $state ): ?bool {
        $name       = $parameter->getName();
        $isVariadic = $parameter->isVariadic();
        $hasType    = $parameter->hasType();
        $state->disablePushTrailingArguments( $isVariadic && $hasType );
        
        // Try to resolve parameter by name
        if ( $state->resolveParameterByName( $name, $isVariadic ) ) {
            return true;
        }
        
        $error = null;
        
        if ( $hasType ) {
            /** @var ReflectionNamedType|ReflectionUnionType|null $reflectionType */
            $reflectionType = $parameter->getType();
            
            /**
             * @psalm-suppress PossiblyNullReference
             *
             * @var ReflectionNamedType[] $types
             */
            $types = $reflectionType instanceof ReflectionNamedType ? [ $reflectionType ] : $reflectionType->getTypes();
            foreach ( $types as $namedType ) {
                try {
                    if ( $this->resolveNamedType( $state, $namedType, $isVariadic, $parameter ) ) {
                        return true;
                    }
                } catch ( NotFoundExceptionInterface $e ) {
                    $error = $e;
                }
            }
        }
        
        if ( $parameter->isDefaultValueAvailable() ) {
            /** @var mixed $argument */
            $argument = $parameter->getDefaultValue();
            $state->addResolvedValue( $argument );
            
            return true;
        }
        
        if ( ! $parameter->isOptional() ) {
            if ( $hasType && $parameter->allowsNull() ) {
                $argument = null;
                $state->addResolvedValue( $argument );
                
                return true;
            }
            
            if ( $error === null ) {
                return false;
            }
            
            // Throw NotFoundExceptionInterface
            throw $error;
        }
        
        if ( $isVariadic ) {
            return true;
        }
        
        return null;
    }
    
    /**
     * @return bool True if argument was resolved
     * @throws NotFoundExceptionInterface
     *
     * @throws ContainerExceptionInterface
     */
    private function resolveNamedType( ResolvingState $state, ReflectionNamedType $parameter, bool $isVariadic, ReflectionParameter $parameterReflection ): bool {
        $type = $parameter->getName();
        /** @psalm-var class-string|null $class */
        $class   = $parameter->isBuiltin() ? null : $type;
        $isClass = $class !== null || $type === 'object';
        
        return $isClass && $this->resolveObjectParameter( $state, $class, $isVariadic, $parameter, $parameterReflection );
    }
    
    /**
     * @psalm-param class-string|null $class
     *
     * @return bool True if argument resolved
     * @throws NotFoundExceptionInterface
     *
     * @throws ContainerExceptionInterface
     */
    private function resolveObjectParameter( ResolvingState $state, ?string $class, bool $isVariadic, ReflectionNamedType $parameter, ReflectionParameter $parameterReflection ): bool {
        $found = $state->resolveParameterByClass( $class, $isVariadic );
        if ( $found || $isVariadic ) {
            return $found;
        }
        
        if ( $class === null ) {
            return false;
        }
        
        try {
            /** @var mixed $argument */
            $argument = $this->container->get( $class );
            $state->addResolvedValue( $argument );
            
            return true;
        } catch ( \Throwable ) {
        }
        
        try {
            $argument = $this->container->getServiceInstancesByTag( sprintf( 'alias:%s $%s', $class, $parameterReflection->getName() ) );
            
            if ( ! empty( $argument ) ) {
                $state->addResolvedValue( $argument[ 0 ] );
                
                return true;
            }
        } catch ( \Throwable) {
        }
        
        return false;
    }
}
