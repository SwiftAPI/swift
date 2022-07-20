<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel;

/**
 * Class KernelDiTags
 * @package Swift\Kernel
 */
class KernelDiTags {

    public const CONTROLLER = 'kernel.controller';
    
    public const CACHE_TYPE = 'kernel.cache.type';
    public const CACHE_DRIVER = 'kernel.cache.driver';

    public const ENTITY = 'kernel.entity';
    public const ENTITY_TYPE = 'kernel.entity.type';

    public const EVENT_SUBSCRIBER = 'kernel.event_subscriber';
    public const EVENT_LISTENER = 'kernel.event_listener';

    public const COMPILER_PASS = 'kernel.compiler_pass';
    public const POST_COMPILER_PASS = 'kernel.post_compiler_pass';

    public const COMMAND = 'kernel.command';

}