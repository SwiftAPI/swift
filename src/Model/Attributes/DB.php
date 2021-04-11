<?php declare(strict_types=1);

namespace Swift\Model\Attributes;

use Attribute;
use JetBrains\PhpStorm\Deprecated;
use Swift\Kernel\Attributes\DI;

/**
 * Class DB
 * @package Swift\Model\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS), DI(exclude: true), Deprecated(replacement: DBTable::class)]
class DB {

    /**
     * DB constructor.
     *
     * @param string $table
     */
    public function __construct(
        /** @var string $table */
        public string $table,
    ) {
    }


}

