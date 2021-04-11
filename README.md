# Swift

This is a simple and fast PHP framework meant to write API's or simple programs in a fast and easy way. This is a not meant as a replacement for other frameworks,
under the hood it uses a lot of the magic from [Symfony](https://symfony.com/), [Dibi](https://github.com/dg/dibi), [Monolog](https://github.com/Seldaek/monolog) and [GraphQl](https://github.com/webonyx/graphql-php/).

### Purpose
This framework is not intended for building websites or big applications. The purpose of this framework is to provide a simple set of basic tools to build simple services, like:
- Simple webservice
- API proxy to bundle several APIs endpoints into one
- Data caching layer
- REST/GraphQl API endpoint for React/Vue front-ends
- Logging service
- CDN

## Getting started
Install the starter (also see documentation)
```php
composer create-project henrivantsant/swift-start project_name
```

Or get it from Composer https://packagist.org/packages/henrivantsant/swift.
```php
composer require henrivantsant/swift
```


## Documentation
Find full documentation at https://henrivantsant.github.io/swift-docs/docs/
