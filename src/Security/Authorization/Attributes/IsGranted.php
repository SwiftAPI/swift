<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Attributes;

/**
 * Class IsGranted
 * @package Swift\Security\Authentication\Attributes
 */
#[\Attribute(\Attribute::TARGET_CLASS, \Attribute::TARGET_METHOD)]
class IsGranted {

    /**
     * IsGranted constructor.
     *
     * @param array $roles
     */
    public function __construct(
        public array $roles
    ) {
    }
}