# Logging (Monolog)
Logging is crucial to know when certain things occur in the application or what errors happen. The guys from [Monolog](https://github.com/Seldaek/monolog) have done an outstanding job at their component. This logging component is a small wrapper around Monolog to easily integrate it with the rest of the system, but feel free to use it directly.

#### Purpose
Provide a simple wrapper, utilities and ready-to-go loggers within the framework. As well as configurable system logging.

## Native logging
Some native loggers come with the package. For more detailed information on usage see [Monolog Documentation](https://github.com/Seldaek/monolog). This will be focused on added/Swift specific features.

### AppLogger
Logs to a /var/app.log file.

### DBLogger
Logs to the log table (`Swift\Logging\Entity\LogEntity`).

### SystemLogger
System logger is mainly meant for the system itself. When extending core functionality this is a useful place. Otherwise, use App/DB Logging or a custom logger.

## Configuration
At this moment configuration is still limited. However it is possible to send logging notifications by mail for error levels from ERROR and higher. This configuration can be set in ``etc/app.yaml``
```yaml
logging:
  enable_mail: true
  logging_mail_from: log@example.com
  logging_mail_to: log@example.com
```

## Custom logger
Easily create your own logger by extending ``Swift\Logging\AbstractLogger``, this extends ``Monolog\Logger`` with some useful functionality and e.g. dispatches certain events. 
```php
declare(strict_types=1);

namespace Swift\Logging;

use Swift\Kernel\Attributes\Autowire;
use Swift\Logging\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

/**
 * Class AppLogger
 * @package Swift\Logging
 */
#[Autowire]
class AppLogger extends AbstractLogger {

    /**
     * AppLogger constructor.
     */
    public function __construct() {
        $stream = new StreamHandler(INCLUDE_DIR . '/var/app.log', AbstractLogger::DEBUG);
        $stream->setFormatter(new LineFormatter());

        parent::__construct('app', array($stream));
    }


}
```

## Events


&larr; [Events & subscribers](https://github.com/HenrivantSant/henri/blob/master/Docs/Events-and-Subscribers.md#8-events--subscribers) | [Authentication](https://github.com/HenrivantSant/henri/blob/master/Docs/Authentication.md#authentication) &rarr;