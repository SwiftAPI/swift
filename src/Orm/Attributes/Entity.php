<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes;

use Attribute;
use Swift\DependencyInjection\Attributes\DI;

/**
 * Class Table
 * @package Swift\Orm\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS), DI(autowire: false)]
#[\AllowDynamicProperties]
final class Entity {
    
    /**
     * Entity constructor.
     *
     * @param string      $table
     * @param string|null $comment
     */
    public function __construct(
        private readonly string  $table,
        private readonly ?string $comment = null,
    ) {
    }

    /**
     * @return string
     */
    public function getTableName(): string {
        return $this->table;
    }
    
    /**
     * @return string|null
     */
    public function getTableComment(): ?string {
        return $this->comment;
    }
    
}

