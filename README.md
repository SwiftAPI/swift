# Swift

[![CodeFactor](https://www.codefactor.io/repository/github/swiftapi/swift/badge)](https://www.codefactor.io/repository/github/swiftapi/swift)
[![Version](http://poser.pugx.org/swift-api/swift/version)](https://packagist.org/packages/swift-api/swift)
[![PHP Version Require](http://poser.pugx.org/swift-api/swift/require/php)](https://packagist.org/packages/swift-api/swift)

This is a small and fast PHP framework meant to write APIs or microservices in a fast and easy manner. This is a not meant as a replacement for other frameworks,
under the hood it uses a lot of the magic from [Symfony](https://symfony.com/), [Cycle](https://github.com/cycle/orm) and [GraphQl](https://github.com/webonyx/graphql-php/).

### Purpose
This framework is not intended for building websites or big applications. The purpose of this framework is to provide a simple set of basic tools to build microservices, like:
- Simple webservice
- API proxy to bundle several APIs endpoints into one or leverage legacy APIs
- Data caching layer
- REST/GraphQl API endpoint for headless front-ends
- Socket API endpoint for real-time communication
- Server Sent Events for real-time communication
- Logging service
- CDN
- etc.

## Getting started
Install the starter (also see documentation)
```php
composer create-project swift-api/swift-start project_name
```

Or get it from Composer https://packagist.org/packages/swift-api/swift.
```php
composer require swift-api/swift
```


## Documentation
Find full documentation at https://swiftapi.github.io/swift-docs/docs/
