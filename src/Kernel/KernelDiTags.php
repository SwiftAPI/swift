<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel;

/**
 * Class KernelDiTags
 * @package Swift\Kernel
 */
class KernelDiTags extends TypeSystem\Enum {

    public const CONTROLLER = 'kernel.controller';
    public const ENTITY = 'kernel.entity';
    public const EVENT_SUBSCRIBER = 'kernel.event_subscriber';
    public const COMPILER_PASS = 'kernel.compiler_pass';
    public const POST_COMPILER_PASS = 'kernel.post_compiler_pass';
    public const COMMAND = 'kernel.command';

}