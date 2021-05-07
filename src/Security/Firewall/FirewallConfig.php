<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall;

use Swift\Configuration\Configuration;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class FirewallConfig
 * @package Swift\Security\Firewall
 */
#[Autowire]
class FirewallConfig implements FirewallConfigInterface {

    /**
     * FirewallConfig constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(
        private Configuration $configuration,
    ) {
    }

}