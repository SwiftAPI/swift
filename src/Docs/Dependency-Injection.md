## 2. Dependency Injection
DI is in the core of the system. It requires barely any configuration. Under the hood [Symfony Dependency Injection](https://symfony.com/doc/current/components/dependency_injection.html) is used to provide this. See the Symfony documentation for more detailed information on the specifics of DI.
```yaml
imports:
  - { resource: vendor/henrivantsant/swift/services.yaml }

parameters:
  # ...

services:
  _defaults:
    autowire: false
    autoconfigure: true
    public: true

  Foo\:
    resource: 'app/Foo/*'
```
This will tell the DI component to enable DI for all files in the app/Foo directory. In the top of the file the framework configuration for DI is included. Make sure to put this services.yaml file in the root of your project. Feel free to split your services.yaml in different files if it grows too big. This can easily be done by using a import statement to the additional services.yaml file, just like framework services.yaml is imported.

### What is dependency injection
Injection usually happens in the constructor. Add the classes you wish to inject as arguments to the constructor and they will be automatically provided as an instance. How convenient! This is available through autowiring. 
```php
declare(strict_types=1);

namespace Foo\Service;

use Swift\Configuration\ConfigurationInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\EntityInterface;
use Swift\Security\Security;

/**
 * Class FooService
 * @package Foo\Service
 */
#[Autowire]
class FooService {

    /**
     * FooService constructor.
     *
     * @param Security $security
     * @param ConfigurationInterface $configuration
     * @param EntityInterface $fooRepository
     * @param string|null $nonAutowired
     */
    public function __construct(
        private Security $security,
        private ConfigurationInterface $configuration,
        private EntityInterface $fooRepository,
        private string|null $nonAutowired = null,
    ) {
    }
    
}
``` 

### Autowiring
Autowiring will make the dependency injection container read the types in the constructor of the class and inject those types when it creates an instance of the class. Through the services.yaml configuration file this can be enabled by default for all classes, or not. There are some important notes to take into consideration for both.

### Autowire Attribute
It is recommended to not autowire all classes by default, but to specifically add the attribute to a class. This prevents for weird bugs in class construction and easily allows for non-autowired classes to exist.

#### Autowire all by default
This is not recommended. If you choose to autowire all classes by default, note that the container will try to inject all types in the constructor. As in the example above, giving a default value will solve this.

Another option is to manually disable autowiring using the DI Attribute
```php
declare(strict_types=1);

namespace Foo\Service;

use Swift\Configuration\ConfigurationInterface;
use Swift\Kernel\Attributes\DI;
use Swift\Model\EntityInterface;
use Swift\Security\Security;
use Foo\Bar\FooBarInterface;

/**
 * Class FooService
 * @package Foo\Service
 */
#[DI(autowire: false)]
class FooService {

    /**
     * FooService constructor.
     *
     * @param Security $security
     * @param ConfigurationInterface $configuration
     * @param EntityInterface $fooRepository
 *   * @param FooBarInterface $fooBar,
     * @param string|null $nonAutowired
     */
    public function __construct(
        private Security $security,
        private ConfigurationInterface $configuration,
        private EntityInterface $fooRepository,
        private FooBarInterface $fooBar,
        private string|null $nonAutowired = null,
    ) {
    }
    
}
```

### Interface injection
To prevent code from becoming to dependent on specific implementations it is recommended to use interfaces instead of direct class references. This however present challenges for autowiring since an interface is not linked to a class implementation, and so the container will need a little help in finding the right class associated to the interface. The container uses 'aliases' which are combination between interface name and variable name to reference to implementing classes.

#### Default alias
By default a camelCase alias will be created according to following example:
`class FooBar implement Foo\Bar\FooBarInterface`

We can now inject this using the interface followed by a camelCase of the implementing class. So this would be `Foo\Bar\FooBarInterface $fooBar`. This is also included in the example above.

It is also possible to create manual aliases, more on this in 'Class aliasing'.

### Setter injection
Setter injection offers some more functionalities over constructor injection for several specific use cases. Setter injection is also dependency injection by the container, but not via de constructor. But through defined class methods called by the container after the classes has been instantiated.

#### When to use setter injection?
This is particularly useful when injection a group of tagged services or when writing abstract or base classes to prevent complex inheritance structures through constructor injection.

```php
declare(strict_types=1);

namespace Foo\Service;

use Swift\Configuration\ConfigurationInterface;
use Swift\HttpFoundation\RequestInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Model\EntityInterface;
use Swift\Security\Security;

/**
 * Class FooService
 * @package Foo\Service
 */
#[DI(tags: ['foo.service', 'foo.example']), Autowire]
class FooService {

    private RequestInterface $request;
    private iterable $services;

    /**
     * FooService constructor.
     *
     * @param Security $security
     * @param ConfigurationInterface $configuration
     * @param EntityInterface $fooRepository
     * @param string|null $nonAutowired
     */
    public function __construct(
        private Security $security,
        private ConfigurationInterface $configuration,
        private EntityInterface $fooRepository,
        private string|null $nonAutowired = null,
    ) {
    }

    #[Autowire]
    public function setAutowired( RequestInterface $request ): void {
        $this->request = $request;
    }

    #[Autowire]
    public function setTaggedServices( #[Autowire(tag: 'example_tag')] iterable $services ): void {
        $this->services = $services;
    }

}
```

### DI and Autowire Attribute
Container configuration happens through the DI Attribute. This comes with multiple configuration options. The Autowire attribute is just to a shortcut to set Autowire to true.

#### Inheritance
Note that attribute settings are inherited from classes, interfaces and traits! Settings can be overwritten. When an implemented interface claims autowire to be false, the class can overwrite this to be true.

```php
declare(strict_types=1);


namespace Swift\Kernel\Attributes;

use Attribute;

/**
 * Class DI
 * @package Swift\Kernel\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class DI {

    /**
     * DI constructor.
     *
     * @param array $tags
     * @param bool $shared
     * @param bool $exclude
     * @param bool $autowire
     * @param array $aliases
     */
    public function __construct(
        public array $tags = array(),
        public bool $shared = true,
        public bool $exclude = false,
        public bool $autowire = true,
        public array $aliases = array(),
    ) {
    }
}
```

#### Class tagging
By tagging services they can be retrieved from the container as a batch. As used on the previous example with Setter Injection.

#### Class shared
By default classes classes are shared. So the container will only make one single instance and inject this in all dependents. By setting shared to false, a new instance will be created every time. This might be useful in some cases.

#### Class exclude
When a class in excluded it will be unknown in the container. Note that tagging for example will also not work on this classes if excluded. If you're not sure what you're doing, it's recommended to set autowire to false instead of excluding.

#### Class autowire
Set the class to autowire or not. It is recommended to always provide either true or false so changing the general autowire settings does not break the application.

#### Class aliasing
A class can have multiple aliases. Those aliases can be type hinted for dependency injection. Usually you'd want to use this to relate interfaces to implementing classes to avoid having to depend on them directly, which would make maintaining the application much harder on long term.

```php
declare(strict_types=1);

namespace Foo\Repository;

use Swift\Kernel\Attributes\DI;
use Swift\Model\Attributes\DB;
use Swift\Model\Entity;
use Swift\Model\EntityInterface;

/**
 * Class FooRepository
 * @package Foo\Repository
 */
#[DI(aliases: [EntityInterface::class . ' $fooRepository']), DB(table: 'foo_bar')]
class FooRepository extends Entity {

}
```

### Compiler passes
It is possible to directly hook into the container compilation and adjust the service definitions in any desired way. Doing this required creating a class tagged with the COMPILER_PASS tag as in the example below from the GraphQl component. When to containers compiles it will iterate through all compiler passes and call the `proces` method with itself as parameter.

```php
declare(strict_types=1);

namespace Swift\GraphQl\Kernel;

use Swift\GraphQl\Attributes\InputType;
use Swift\GraphQl\Attributes\Mutation;
use Swift\GraphQl\Attributes\Query;
use Swift\GraphQl\Attributes\Type;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\DiTags;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class GraphQlCompilerPass
 * @package Swift\GraphQl\Kernel
 */
#[DI(tags: [DiTags::COMPILER_PASS])]
class GraphQlCompilerPass implements CompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ): void {
        foreach ($container->getDefinitions() as $definition) {
            $classReflection = $container->getReflectionClass($definition->getClass());

            if (!empty($classReflection?->getAttributes(name: Type::class))) {
                $definition->addTag(name: 'graphql.type');
            }

            if (!empty($classReflection?->getAttributes(name: InputType::class))) {
                $definition->addTag( name: 'graphql.input_type' );
            }

            foreach ($classReflection?->getMethods() as $reflectionMethod) {
                if (!empty($reflectionMethod->getAttributes(name: Query::class))) {
                    $definition->addTag(name: 'graphql.query');
                }
                if (!empty($reflectionMethod->getAttributes(name: Mutation::class))) {
                    $definition->addTag(name: 'graphql.mutation');
                }
            }
        }
    }
}
```

&larr; [Routing](https://github.com/HenrivantSant/henri/blob/master/Docs/Routing.md#1-routing) | [Configuration](https://github.com/HenrivantSant/henri/blob/master/Docs/Configuration.md#3-configuration) &rarr;