## 3. Configuration
The system uses [Yaml](https://yaml.org/) for configuration.
### Basic setup
The basic app configuration setup is as below. Place this config.yaml file in the etc/config/ directory of your project. The configuration comes with three configuration which are necessary. Security will be preset for you and you don't need to change anything here if there's no need.
#### app.yaml
```yaml
app:
  # App name
  name: API Henri
  # App mode (either develop or production)
  mode: develop
  # Enable/disable debug mode
  debug: true
  # Allow cross origin requests (returns 200 response for OPTIONS Request if no Route is matched)
  allow_cors: true
  # Default application timezone
  timezone: Europe/Amsterdam

routing:
  # Base url used in route matching. This is also useful on sub domains
  baseurl: api2.henrivantsant.com

graphql:
  # Enable/disable introspection for graphql
  enable_introspection: true

logging:
  # Enable/disable mails for logger
  enable_mail: true
  # Mail from address
  logging_mail_from: log@henrivantsant.com
  # Mail to address
  logging_mail_to: log@henrivantsant.com
```
#### database.yaml
For more on the actual working of this, see the Database component.
```yaml
connection:
  driver: mysqli
  host: localhost
  username: root
  password: ''
  database: foo_bar
  port: 3306
  prefix: 4593g_
```
#### security.yaml
For more on the actual working of this, see the Security component.
```yaml
enable_firewalls: true

firewalls:
  main:
    # limit login attempts, defaults to 5 per minute. Set to 0 to disable throttling
    login_throttling:
      max_attempts: 5

role_hierarchy:
  ROLE_GUEST:
  ROLE_USER:
  ROLE_CLIENT: ['ROLE_USERS_LIST']
  ROLE_ADMIN: ['ROLE_USERS_LIST']
  ROLE_SUPER_ADMIN: ['ROLE_ADMIN']

access_decision_manager:
  strategy: Swift\Security\Authorization\Strategy\AffirmativeDecisionStrategy
  allow_if_all_abstain: false

access_control:
```
#### Using app configuration
It is not unthinkable that you might want some configuration for you specific app. This is possible. First make sure to add a config.yaml file the app directory. This is the entry point for app configuration. Here you import your app config.
```yaml
imports:
  - { resource: app/Foo/config.yaml }
```
In the app/Foo directory also place a config.yaml file (as imported above)
```yaml
foo:
    bar: example
    lorem: ipsum
```

### Configuration scopes
The configuration is build in scopes. The root configuration has a scope as well, but can be ignored. The configuration in the file 'app/Foo/config.yaml' Will be in the scope 'app/Foo'. The idea behind is to isolate configuration in groups.

### Reading the configuration
To read the configuration you will have to inject the `Swift\Configuration\Configuration` class (or `private Swift\Configuration\ConfigurationInterface $configuration`). Simply calling the 'get' method is enough. To get the value of bar from the example above would work like this `$this->configuration->get('foo.bar', 'app/Foo');`. The first argument is the name of the setting and the second one is the scope. Reading the root configuration uses the 'root' as scope. Checking whether the app in debug mode would work like this `$this->configuration->get('app.debug', 'app');` or getting the database username: `$this->configuration->get('database.username', 'database');`.

### Writing the configuration (from code)
Writing the configuration works in the exact same matter. Note that is not possible to write to non-existing settings. Make sure the already exist before. 

Writing to the foo.bar setting as above would work as `$this->configuration->set('foo.bar', 'writing example', 'app/Foo');`. Note that this works exactly the same as getting a setting, except now the second parameter is the new value you wish to assign.

&larr; [Dependency Injection](https://github.com/HenrivantSant/henri/blob/master/Docs/Dependency-Injection.md#2-dependency-injection) | [Database handling](https://github.com/HenrivantSant/henri/blob/master/Docs/Database.md#4-database-handling) &rarr;