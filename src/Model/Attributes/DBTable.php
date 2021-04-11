<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Attributes;

use Attribute;
use Swift\Kernel\Attributes\DI;

/**
 * Class DB
 * @package Swift\Model\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS), DI(exclude: true)]
class DBTable {

    /**
     * DBTable constructor.
     *
     * @param string $name
     */
    public function __construct(
        public string $name,
    ) {
    }


}

