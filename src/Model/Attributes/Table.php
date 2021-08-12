<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Attributes;

use Attribute;
use Swift\Kernel\Attributes\DI;

/**
 * Class Table
 * @package Swift\Model\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS), DI(autowire: false)]
final class Table {

    /**
     * Table constructor.
     *
     * @param string $name
     */
    public function __construct(
        public string $name,
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }


}

