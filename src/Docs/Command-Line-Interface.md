# 6. Command line interface
The command line presents a clean implementation of the [Symfony Command Line](https://symfony.com/doc/current/components/console.html). See their documentation on how to use the command line general.

## Setup
It's requires a small setup to get to command line running.
1. Create a folder 'bin' in your project root
2. Create a file (without extension) named 'console' with the content from below
```php
#!/usr/bin/env php
<?php
require_once 'vendor/autoload.php';

use Swift\Console\CLIApplication;

$CLIApplication = new CLIApplication();
$CLIApplication->run();
```
This should be all. Open your command line in the root of the project and run ``php bin/console list``.

## Default commands
The system comes with a batch of useful commands. Get a list of all available commands by running `php bin/console list` from the command line in the root of your project. The specifics of each command will be explained in their respective chapters.

## Creating a custom command
It is very easy to add your own command for running tasks, changing settings, creating cron commands, etc.

#### Output
Symfony automatically provides a 'simple' output helper as the second argument for the execute method. If you desire some more styling to your command or for e.g. tables. Abstract Command already populated a [Symfony Style](https://symfony.com/doc/current/console/style.html) class ready to use e.g. ``$this->io->title(...)``.

```php
declare(strict_types=1);

namespace Foo\Command;

use Swift\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FooCommand
 * @package Foo\Command
 */
class FooCommand extends AbstractCommand {

    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        // the name of the command (the part after "bin/console")
        return 'foo:bar';
    }

    /**
     * Configure command 
     */
    protected function configure(): void {
        $this
            ->setDescription('Command description')

            ->setHelp('Explanatory information about command')
        ;
    }

    /**
     * Entry point for command execution
     * 
     * @param InputInterface $input     Input for command
     * @param OutputInterface $output   Output helper for command
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->io->writeln('Foo bar');

        return AbstractCommand::SUCCESS; // OR AbstractCommand::FAILURE
    }

}
```

&larr; [Making (curl) requests](https://github.com/HenrivantSant/henri/blob/master/Docs/Making-Requests.md#5-making-curl-requests) | [Annotations](https://github.com/HenrivantSant/henri/blob/master/Docs/Annotations.md#7-annotations) &rarr; 