# GraphQL

The GraphQl component integrates a GraphQl endpoint as alternative for REST endpoints. Queries and Mutation can both be registered as methods on controllers.

The GraphQl implementation fully complies with the [GraphQl Spec](http://spec.graphql.org/) and is basically a wrapper for [Webonxy GraphQl](https://github.com/webonyx/graphql-php).

Types, Queries and Mutations are mapped through the use of Attributes. Besides this some handy utilities have been added the make it all work nicely with the rest of the framework.

Security endpoints are provided out of the box. So client and user authentication will work right away.

Note that the documentation provided by Webonyx is only partially helpful since Webonyx is a almost literal translation of the Javascript Spec it's functional by design, where Swift is Object Oriented. The component wraps Webonyx in Object Oriented way and provides utilities for easy usage and integration to the rest of the application.

## Endpoint
GraphQl can be enabled with setting this is in the etc/app.yaml config. This will automatically enable the /graphql endpoint.
```yaml
graphql:
    enable_introspection: true
```

## Tools
A tool that is highly recommended for building GraphQl endpoints is [GraphiQL](https://github.com/graphql/graphiql). This will inspect your endpoint, visual the schema and let's you execute queries and mutations against your schema.

## Attributes
Attributes are used to tell the compiler about possible types, fields, queries, mutations, etc. On compilation all those are mapped and stored in Type Registries.

### Query
Queries are the entry points in your Schema. A Query can easily be defined as below by adding the Query Attribute. The compiler will use PHP Types to translate this to types for the Schema. However some fields can be overridden or specific differently if desired. Those settings will take precedence over PHP Types if provided. See below for the options.

```php
declare(strict_types=1);

namespace Foo\Controller;

use Foo\Type\BarType;
use Foo\Type\FooType;
use Swift\Controller\AbstractController;
use Swift\GraphQl\Attributes\Query;

/**
 * Class FooControllerGraphQl
 * @package Foo\Controller
 */
class FooControllerGraphQl extends AbstractController {

    /**
     * @param FooType $foo
     *
     * @return BarType
     */
    #[Query]
    public function foo( FooType $foo ): BarType {
        // Fetch some data here

        return new BarType(id: $foo->id, title: 'GraphQl result title');
    }

}
```

```php
declare(strict_types=1);

namespace Swift\GraphQl\Attributes;

use Attribute;
use Swift\Kernel\Attributes\DI;

/**
 * Class Query
 * @package Swift\GraphQl\Attributes
 */
#[Attribute(Attribute::TARGET_METHOD), DI(exclude: true)]
class Query {

    /**
     * Query constructor.
     *
     * @param string|null $name
     * @param mixed $type
     * @param bool $nullable
     * @param bool $isList
     * @param string|null $generator
     * @param array $generatorArguments
     * @param string|null $description
    */
    public function __construct(
        public string $name = null,
        public mixed $type = null,
        public bool $nullable = true,
        public bool $isList = false,
        public string $generator = null,
        public array $generatorArguments = array(),
        public string|null $description = null,
    ) {
    }
}
```

#### Foo example query in action
_Request:_
```graphql
query {
    Foo(foo: {id: "3"}) {
        id
        title
        author {
            id
            name
        }
        reviews(limit: 3) {
            id
            username
            content
        }
    }
}
```
_Response:_
```json
{
  "data": {
    "Foo": {
      "id": "3",
      "title": "GraphQl result title",
      "author": {
        "id": "3",
        "name": "Foo Bar"
      },
      "reviews": [
        {
          "id": "1",
          "username": "Foo",
          "content": "Lorem ipsum dolor"
        },
        {
          "id": "2",
          "username": "Bar",
          "content": "Lorem ipsum dolor"
        },
        {
          "id": "3",
          "username": "Fubar",
          "content": "Lorem ipsum dolor"
        }
      ]
    }
  }
}
```

### Mutation
Mutations are created in a very similar way. 

```php
declare(strict_types=1);

namespace Foo\Controller;

use Foo\Type\BarInput;
use Foo\Type\BarType;
use Swift\Controller\AbstractController;
use Swift\GraphQl\Attributes\Mutation;

/**
 * Class FooControllerGraphQl
 * @package Foo\Controller
 */
class FooControllerGraphQl extends AbstractController {

    /**
     * @param BarInput $bar
     *
     * @return BarType
     */
    #[Mutation]
    public function createBar( BarInput $bar ): BarType {
        // Create new entity based on input and return it's values

        return new BarType(id: '4', title: $bar->title);
    }

}
```

#### createBar mutation in action
_Request:_
```graphql
mutation {
    CreateBar(bar: {title: "Demo Bar"}) {
        id
        title
    }
}
```
_Response:_
```json
{
    "data": {
        "CreateBar": {
            "id": "4",
            "title": "Demo Bar"
        }
    }
}
```

### Type
As seen in the example above we're using a defined types and not one the default composites. In order to use those specific types we need to let the compiler know those exist on before hand. This is done with the Type Attribute. This will mark the given class as an OutputType.

In the example we're keeping it simple, but you might just as well link to other types here link for example the Author of Bar.

#### Default value / nullable
By giving a field default value it will automatically be nullable too. By optionally having it null, it will 'just' be nullable.

```php
declare(strict_types=1);

namespace Foo\Type;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\EntityInterface;

/**
 * Class BarType
 * @package Foo\Type
 */
#[Type]
class BarType {

    private EntityInterface $fooRepository;

    /**
     * FooType constructor.
     *
     * @param string $id
     * @param string $title
     */
    public function __construct(
        #[Field] public string $id,
        #[Field] public string $title,
    ) {
    }

    #[Field(name: 'author', description: 'This is a field description')]
    public function getAuthor(): AuthorType {
        return new AuthorType(id: '3', name: 'Foo Bar');
    }

    #[Field(name: 'reviews', type: ReviewType::class, isList: true)]
    public function getReviews(int $limit = 5): array {
        return array_slice(array(
            new ReviewType(id: '1', username: 'Foo', content: 'Lorem ipsum dolor'),
            new ReviewType(id: '2', username: 'Bar', content: 'Lorem ipsum dolor'),
            new ReviewType(id: '3', username: 'Fubar', content: 'Lorem ipsum dolor'),
        ), 0, $limit);
    }

    #[Autowire]
    public function setFooRepository(EntityInterface $fooRepository): void {
        $this->fooRepository = $fooRepository;
    }

}
```
```php
declare(strict_types=1);

namespace Swift\GraphQl\Attributes;

use Attribute;
use Swift\Kernel\Attributes\DI;

/**
 * Class Type
 * @package Swift\GraphQl\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS), DI(exclude: true)]
class Type {

    /**
     * Type constructor.
     *
     * @param string|null $name
     * @param string|null $extends
     * @param string|null $generator
     * @param array $generatorArguments
     * @param string|null $description
     */
    public function __construct(
        public ?string $name = null,
        public ?string $extends = null,
        public ?string $generator = null,
        public array $generatorArguments = array(),
        private string|null $description = null,
    ) {
    }
}
```

### InputType
An InputType quite surprisingly registers the Attributed class to an InputType. Those can be used as the arguments/input for queries and mutations.

```php
declare(strict_types=1);

namespace Foo\Type;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\InputType;
use Swift\GraphQl\Types\Type;

/**
 * Class FooType
 * @package Foo\Type
 */
#[InputType]
class FooType {

    /**
     * FooType constructor.
     *
     * @param string $id
     */
    public function __construct(
        #[Field(type: Type::ID)] public string $id,
    ) {
    }

}
```
```php
declare(strict_types=1);

namespace Swift\GraphQl\Attributes;

use Attribute;
use Swift\Kernel\Attributes\DI;

/**
 * Class Type
 * @package Swift\GraphQl\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS), DI(exclude: true)]
class InputType {

    /**
     * Type constructor.
     *
     * @param string|null $name
     * @param string|null $extends
     * @param string|null $generator
     * @param array $generatorArguments
     * @param string|null $description 
     */
    public function __construct(
        public ?string $name = null,
        public ?string $extends = null,
        public ?string $generator = null,
        public array $generatorArguments = array(),
        public string|null $description = null,
    ) {
    }
}
```

### Field
The field annotation is used to let the compiled know a property or method should be registered as a field within a Type or InputType. This only works within classes having the either the Type o InputType Attribute.
```php
declare(strict_types=1);

namespace Foo\Type;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;

/**
 * Class BarType
 * @package Foo\Type
 */
#[Type]
class BarType {

    /**
     * FooType constructor.
     *
     * @param string $id
     * @param string $title
     */
    public function __construct(
        #[Field] public string $id,
        #[Field] public string $title,
    ) {
    }
    
    #[Field(name: 'author', description: 'This is a field description')]
    public function getAuthor(): AuthorType {
        return new AuthorType(id: '3', name: 'Foo Bar');
    }

    #[Field(name: 'reviews', type: ReviewType::class, isList: true)]
    public function getReviews(int $limit = 5): array {
        return array_slice(array(
            new ReviewType(id: '1', username: 'Foo', content: 'Lorem ipsum dolor'),
            new ReviewType(id: '2', username: 'Bar', content: 'Lorem ipsum dolor'),
            new ReviewType(id: '3', username: 'Fubar', content: 'Lorem ipsum dolor'),
        ), 0, $limit);
    }

}
```

### Argument
The Argument attribute can be used to add additional meta data or settings to mutation or query arguments. They also serve to link to generators. They example below shows the GraphQl endpoint to list all users. It uses a generator to dynamically create a complex input type based on the OutputType and the Entity it references to.
```php
/**
 * GraphQl endpoint for listing users
 *
 * @param array $filter
 *
 * @return UserConnection
 */
#[Query(name: 'Users', description: 'List all users' )]
public function users( #[Argument(type: ArgumentsType::class, generator: EntityArgumentGenerator::class, generatorArguments: ['entity' => UserEntity::class])] array $filter ): UserConnection {
    // Make sure a user is authenticated
    $this->denyAccessUnlessGranted([AuthorizationRolesEnum::ROLE_USERS_LIST]);

    $filter ??= array();
    $state = $filter['where'] ?? array();
    unset($filter['where']);
    $argumentsType = new ArgumentsType(...$filter);

    if (!$result = $this->userDatabaseStorage->findMany($state, $argumentsType->toArgument())) {
        return new UserConnection($result);
    }


    return new UserConnection($result);
}
```
```php
declare(strict_types=1);

namespace Swift\GraphQl\Attributes;

use Attribute;

/**
 * Class Argument
 * @package Swift\GraphQl\Attributes
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Argument {

    /**
     * Argument constructor.
     *
     * @param string|null $name argument name in the schema
     * @param string|array|null $type array of types will lead to a union
     * @param string|null $generator FQN of the generator class 
     * @param array|null $generatorArguments Arguments to be passed to the generator
     * @param string|null $description 
     */
    public function __construct(
        public string|null $name = null,
        public string|array|null $type = null,
        public string|null $generator = null,
        public array|null $generatorArguments = null,
        public string|null $description = null,
    ) {
    }
}
```

### Generators
No feature freeze just yet. Documentation will follow.

## Autowire in types
Since type definitions should be able to be created easily the constructor can not directly depend on Dependency Injection from the container. However method injection is supported.

Once the Field Resolver calls the Type to resolve a given field it will call all Autowire methods. Note that this happens on Field Resolution and not on Container Compilation. So you can only depend on the Services being set in the actual Field methods.

```php
declare(strict_types=1);

namespace Foo\Type;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\EntityInterface;

/**
 * Class BarType
 * @package Foo\Type
 */
#[Type]
class BarType {

    private EntityInterface $fooRepository;

    /**
     * FooType constructor.
     *
     * @param string $id
     * @param string $title
     */
    public function __construct(
        #[Field] public string $id,
        #[Field] public string $title,
    ) {
    }

    #[Field(name: 'author', description: 'This is a field description')]
    public function getAuthor(): AuthorType {
        return new AuthorType(id: '3', name: 'Foo Bar');
    }

    #[Field(name: 'reviews', type: ReviewType::class, isList: true)]
    public function getReviews(int $first = 5): array {
        // Here we can assume $this->fooRepository to autowired
        return array_slice(array(
            new ReviewType(id: '1', username: 'Foo', content: 'Lorem ipsum dolor'),
            new ReviewType(id: '2', username: 'Bar', content: 'Lorem ipsum dolor'),
            new ReviewType(id: '3', username: 'Fubar', content: 'Lorem ipsum dolor'),
        ), 0, $first);
    }

    #[Autowire]
    public function setFooRepository(EntityInterface $fooRepository): void {
        $this->fooRepository = $fooRepository;
    }

}
```

## Schema generation
It might be handy or even needed to generate a schema.graphql file of your api in actual Type Language. This is easily done by running: 
```bash
bin/console graphql:schema:dump
```
This will automatically generate graphql schema representation of the api in the /etc directory.

The Abstract classes enforce you to create a little logic and create all common fields and handle ID encoding too.

## Relay Server Spec
It is recommended to follow the [Relay Server Specs](https://relay.dev/docs/guides/graphql-server-specification/) in the schema. All default Swift endpoint will comply with this spec out of the box. There's some useful abstracts and interfaces to help you on the way.
- ``Swift\GraphQl\Types\AbstractConnectionType`` or ``Swift\GraphQl\Types\ConnectionTypeInterface``
- ``Swift\GraphQl\Types\AbstractEdgeType`` or ``Swift\GraphQl\Types\EdgeInterface``
- ``Swift\GraphQl\Types\NodeTypeInterface``  
  As the Relay Spec defines any Node Type should be able to resolve against the Interface. To be able to resolve this the Node Type should return a resolver class and method for the given type. See example below.
  _Type_
  ```php
    declare(strict_types=1);
    
    namespace Swift\Security\User\Type;
    
    use DateTime;
    use Swift\GraphQl\Attributes\Field;
    use Swift\GraphQl\Attributes\Type;
    use Swift\GraphQl\ContextInterface;
    use Swift\GraphQl\Types\NodeTypeInterface;
    use Swift\GraphQl\Utils;
    use Swift\Kernel\Attributes\DI;
    use Swift\Security\User\Controller\UserControllerGraphQl;
    
    /**
    * Class UserType
    * @package Swift\Security\User\Type
      */
    #[DI(autowire: false), Type(description: 'Represents user data')]
    class UserType implements NodeTypeInterface {
    
        /**
         * UserType constructor.
         *
         * @param int|null $id
         * @param string $username
         * @param string|null $email
         * @param string $firstname
         * @param string $lastname
         * @param DateTime $created
         * @param DateTime $modified
         * @param string|null $password
         */
        public function __construct(
            public ?int $id,
            #[Field] public string $username,
            #[Field(nullable: true)] public ?string $email,
            #[Field] public string $firstname,
            #[Field] public string $lastname,
            #[Field] public DateTime $created,
            #[Field] public DateTime $modified,
            private string|null $password = null,
        ) {
        }
    
        #[Field( name: 'id', description: 'The user ID' )]
        public function getId(): string {
             return Utils::encodeId('UserType', $this->id);
        }
    
        /**
         * @inheritDoc
         */
        public static function getNodeResolverClassnameAndMethod( int|string $id, ContextInterface $context ): array {
            return [UserControllerGraphQl::class, 'getUserTypeByNode'];
        }
    
    }
  ```
  _Resolver method in referred controller (Swift\Security\User\Controller\UserControllerGraphQl)_
  ```php
    /**
    * Node field resolver callback function for UserType
    *
    * @param string|int $id
    * @param ContextInterface $context
    *
    * @return UserType
    */
    public function getUserTypeByNode( string|int $id, ContextInterface $context ): UserType {
        // Make sure a user is authenticated
        $this->denyAccessUnlessGranted([AuthorizationTypesEnum::IS_AUTHENTICATED, AuthorizationRolesEnum::ROLE_USERS_LIST]);
    
        // Get user data
        if (!$data = $this->userProvider->getUserById((int) $id)?->serialize()) {
            throw new UserNotFoundException(sprintf('User with id %s not found', $id));
        }

        return new UserType(...(array) $data);
    }
  ```
- ``Swift\GraphQl\Types\PageInfoType``  
    Use ``Swift\GraphQl\Types\PageInfoType->toArgument()`` to build an Argument() class for filter usage on any Entity.  
  _Example usage of ArgumentsType to Argument_
  ```php
    /**
     * GraphQl endpoint for listing users
     *
     * @param array $filter
     *
     * @return UserConnection
     */
    #[Query(name: 'Users', description: 'List all users' )]
    public function users( #[Argument(type: ArgumentsType::class, generator: EntityArgumentGenerator::class, generatorArguments: ['entity' => UserEntity::class])] array $filter ): UserConnection {
        // Make sure a user is authenticated
        $this->denyAccessUnlessGranted([AuthorizationRolesEnum::ROLE_USERS_LIST]);
    
        $filter ??= array();
        $state = $filter['where'] ?? array();
        unset($filter['where']);
        $argumentsType = new ArgumentsType(...$filter);
    
        if (!$result = $this->userDatabaseStorage->findMany($state, $argumentsType->toArgument())) {
            return new UserConnection($result);
        }
    
    
        return new UserConnection($result);
    }
  ```

&larr; [Users](https://github.com/HenrivantSant/henri/blob/master/Docs/Users.md#users)