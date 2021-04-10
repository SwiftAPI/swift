<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel;

use Swift\Kernel\Container\Container;

/**
 * Trait ContainerAwareTrait
 * @package Swift\Kernel
 */
trait ContainerAwareTrait {

    protected Container $container;

    /**
     * ContainerAwareTrait constructor.
     */
    public function __construct() {
        global $container;
        $this->container = $container;
    }

}