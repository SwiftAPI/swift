<?php


namespace Swift\Kernel;

use Swift\Kernel\TypeSystem\Enum;

class DiTags extends Enum {

    public const CONTROLLER = 'kernel.controller';
    public const ENTITY = 'kernel.entity';
    public const EVENT_SUBSCRIBER = 'kernel.event_subscriber';
    public const COMPILER_PASS = 'kernel.compiler_pass';

}