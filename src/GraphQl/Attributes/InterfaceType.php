<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Attributes;

use JetBrains\PhpStorm\Deprecated;

/**
 * Class InterfaceType
 * @package Swift\GraphQl\Attributes
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
#[Deprecated]
#[\AllowDynamicProperties]
class InterfaceType {

    /**
     * InterfaceType constructor.
     *
     * @param string|null $name
     * @param string|null $description
     */
    public function __construct(
        public string|null $name = null,
        public string|null $description = null,
    ) {
        if (is_null($this->name)) {
            $this->name = (new \ReflectionClass($this))->getShortName();
        }
    }

}