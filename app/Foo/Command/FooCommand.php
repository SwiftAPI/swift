<?php

namespace Foo\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FooCommand extends Command {

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'foo:bar';

    protected function configure()
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...

        return 0;
    }

}