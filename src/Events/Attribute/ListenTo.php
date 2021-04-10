<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Events\Attribute;

use Swift\Kernel\Attributes\DI;

/**
 * Class ListenTo
 * @package Swift\Event\Attribute
 */
#[\Attribute(\Attribute::TARGET_METHOD), DI(exclude: true)]
class ListenTo {

    /**
     * ListenTo constructor.
     *
     * @param string $event
     * @param int $priority
     */
    public function __construct(
        public string $event,
        public int $priority = 0,
    ) {
    }

}