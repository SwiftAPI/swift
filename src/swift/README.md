# henri

## Looking for contributors!

## Intro
This is a simple, fast and basic PHP framework meant to write API's or simple programs in a fast and easy way. This is a not meant as a replacement for other frameworks,
under the hood it uses a lot of the magic from [Symfony](https://symfony.com/), [Dibi](https://github.com/dg/dibi), [Unirest](https://github.com/Kong/unirest-php), [Monolog](https://github.com/Seldaek/monolog) and [Firebase](https://github.com/firebase/php-jwt).

Get it from Composer https://packagist.org/packages/henrivantsant/henri.
```php
composer require henrivantsant/henri
```

### Purpose  
This framework is not intended for building websites or big applications. The purpose of this framework is to provide a simple set of basic tools to build simple services, like: 
- Simple webservice
- API proxy to bundle several APIs endpoints into one
- Data caching layer
- API endpoint for React/Vue front-ends
- Logging service
- CDN

## Table of content
1. [Routing](https://github.com/HenrivantSant/henri/blob/master/Docs/Routing.md#1-routing)
	1. [Controllers](https://github.com/HenrivantSant/henri/blob/master/Docs/Routing.md#controllers)
	1. [Route annotation](https://github.com/HenrivantSant/henri/blob/master/Docs/Routing.md#route-annotation)
	1. [Responses](https://github.com/HenrivantSant/henri/blob/master/Docs/Routing.md#responses)
	1. [Exceptions](https://github.com/HenrivantSant/henri/blob/master/Docs/Routing.md#exceptions)
	1. [Hooking in to the router (route events)](https://github.com/HenrivantSant/henri/blob/master/Docs/Routing.md#route-annotation)
1. [Dependency Injection](https://github.com/HenrivantSant/henri/blob/master/Docs/Dependency-Injection.md#2-dependency-injection)
    1. [How to inject](https://github.com/HenrivantSant/henri/blob/master/Docs/Dependency-Injection.md#how-to-inject)
1. [Configuration](https://github.com/HenrivantSant/henri/blob/master/Docs/Configuration.md#3-configuration)
	1. [Basic setup](https://github.com/HenrivantSant/henri/blob/master/Docs/Configuration.md#basic-setup)
	1. [Configuration scopes](https://github.com/HenrivantSant/henri/blob/master/Docs/Configuration.md#configuration-scopes)
	1. [Reading the configuration](https://github.com/HenrivantSant/henri/blob/master/Docs/Configuration.md#reading-the-configuration)
	1. [Writing the configuration](https://github.com/HenrivantSant/henri/blob/master/Docs/Configuration.md#writing-the-configuration-in-code)
1. [Database handling](https://github.com/HenrivantSant/henri/blob/master/Docs/Database.md#4-database-handling)
	1. Database layer
	1. Entities
	1. Entity Manager
	1. Entity Manager List
	1. Command line interface
1. [Making (curl) requests](https://github.com/HenrivantSant/henri/blob/master/Docs/Making-Requests.md#5-making-curl-requests)
	1. Request service
1. [Command Line](https://github.com/HenrivantSant/henri/blob/master/Docs/Command-Line-Interface.md#6-command-line-interface)
	1. [Setup](https://github.com/HenrivantSant/henri/blob/master/Docs/Command-Line-Interface.md#setup)
	1. [Default commands](https://github.com/HenrivantSant/henri/blob/master/Docs/Command-Line-Interface.md#default-commands)
	1. [Create your own commands](https://github.com/HenrivantSant/henri/blob/master/Docs/Command-Line-Interface.md#create-your-own-commands)
1. [Annotations](https://github.com/HenrivantSant/henri/blob/master/Docs/Annotations.md#7-annotations)
	1. [What & why annotations](https://github.com/HenrivantSant/henri/blob/master/Docs/Annotations.md#what--why-annotations)
	1. [How use your own annotations](https://github.com/HenrivantSant/henri/blob/master/Docs/Annotations.md#how-use-your-own-annotations)
1. [Events & subscribers](https://github.com/HenrivantSant/henri/blob/master/Docs/Events-and-Subscribers.md#8-events--subscribers)
	1. [Default system events](https://github.com/HenrivantSant/henri/blob/master/Docs/Events-and-Subscribers.md#default-system-events)
	1. [How to subscribe to events](https://github.com/HenrivantSant/henri/blob/master/Docs/Events-and-Subscribers.md#how-to-subscribe-to-events)
	1. [How to create your own events](https://github.com/HenrivantSant/henri/blob/master/Docs/Events-and-Subscribers.md#how-to-create-your-own-events)
	1. [Dispatch events](https://github.com/HenrivantSant/henri/blob/master/Docs/Events-and-Subscribers.md#dispatch-events)
1. [Logging (Monolog)](https://github.com/HenrivantSant/henri/blob/master/Docs/Logging.md#logging)
	1. Native logging
	1. Configuration
	1. Ways of logging
	1. Use your logger
1. [Authentication](https://github.com/HenrivantSant/henri/blob/master/Docs/Authentication.md#authentication)
	1. Authentication levels
	1. API Key
	1. JWT
	1. User logins
	1. Add your level and/or authentication
1. [Users](https://github.com/HenrivantSant/henri/blob/master/Docs/Users.md#users)
	1. User management
	1. Create a user
	1. Update user
	1. User authentication
1. [GraphQL](https://github.com/HenrivantSant/henri/blob/master/Docs/GraphQL.md#graphql)	
1. What's next!
	1. Native logging interface (status: in development)
	1. Out of the box GraphQL support (status: expected early 2021)
	1. Support websockets
	1. PHP8 Compatibility (status: expected early 2021)
	1. Overriding framework classes by setting preferences to the container (status: no expection yet)
	1. Influence DI behaviour using Annotations (status: no expectation yet)
	1. Default annotation reading service with PHP8 Annotations support (status: no expectation yet)
	1. Support websockets
