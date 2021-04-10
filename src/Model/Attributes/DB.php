<?php declare(strict_types=1);

namespace Swift\Model\Attributes;

use Attribute;
use Swift\Kernel\Attributes\DI;

/**
 * Class DB
 * @package Swift\Model\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS), DI(exclude: true)]
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

