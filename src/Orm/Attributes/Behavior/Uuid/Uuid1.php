<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Behavior\Uuid;

use Swift\Orm\Behavior\Listener\Uuid\Uuid1 as Listener;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Type\Hexadecimal;

/**
 * Uses a version 1 (time-based) UUID from a host ID, sequence number,
 * and the current time
 */
#[\Attribute( \Attribute::TARGET_CLASS ), NamedArgumentConstructor]
final class Uuid1 extends Uuid {
    
    /**
     * @param non-empty-string            $field    Uuid property name
     * @param non-empty-string|null       $column   Uuid column name
     * @param Hexadecimal|int|string|null $node     A 48-bit number representing the
     *                                              hardware address; this number may be represented as an integer or a
     *                                              hexadecimal string
     * @param int|null                    $clockSeq A 14-bit number used to help avoid duplicates
     *                                              that could arise when the clock is set backwards in time or if the
     *                                              node ID changes
     *
     * @see \Ramsey\Uuid\UuidFactoryInterface::uuid1()
     */
    public function __construct(
        string                                       $field = 'uuid',
        ?string                                      $column = null,
        private readonly Hexadecimal|int|string|null $node = null,
        private readonly ?int                        $clockSeq = null
    ) {
        $this->field  = $field;
        $this->column = $column;
    }
    
    protected function getListenerClass(): string {
        return Listener::class;
    }
    
    #[ArrayShape( [ 'field' => 'string', 'node' => 'int|string|null', 'clockSeq' => 'int|null' ] )]
    protected function getListenerArgs(): array {
        return [
            'field'    => $this->field,
            'node'     => $this->node instanceof Hexadecimal ? (string) $this->node : $this->node,
            'clockSeq' => $this->clockSeq,
        ];
    }
    
}
