<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Behavior\Uuid;

use Swift\Orm\Behavior\Listener\Uuid\Uuid2 as Listener;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;

/**
 * Uses a version 2 (DCE Security) UUID from a local domain, local
 * identifier, host ID, clock sequence, and the current time
 */
#[\Attribute( \Attribute::TARGET_CLASS ), NamedArgumentConstructor]
#[\AllowDynamicProperties]
final class Uuid2 extends Uuid {
    
    /**
     * @param int                       $localDomain     The local domain to use when generating bytes,
     *                                                   according to DCE Security
     * @param non-empty-string          $field           Uuid property name
     * @param non-empty-string|null     $column          Uuid column name
     * @param IntegerObject|string|null $localIdentifier The local identifier for the
     *                                                   given domain; this may be a UID or GID on POSIX systems, if the local
     *                                                   domain is person or group, or it may be a site-defined identifier
     *                                                   if the local domain is org
     * @param Hexadecimal|string|null   $node            A 48-bit number representing the hardware
     *                                                   address
     * @param int|null                  $clockSeq        A 14-bit number used to help avoid duplicates
     *                                                   that could arise when the clock is set backwards in time or if the
     *                                                   node ID changes
     *
     * @see \Ramsey\Uuid\UuidFactoryInterface::uuid2()
     */
    public function __construct(
        private readonly int                       $localDomain,
        string                                     $field = 'uuid',
        ?string                                    $column = null,
        private readonly IntegerObject|string|null $localIdentifier = null,
        private readonly Hexadecimal|string|null   $node = null,
        private readonly ?int                      $clockSeq = null
    ) {
        $this->field  = $field;
        $this->column = $column;
    }
    
    protected function getListenerClass(): string {
        return Listener::class;
    }
    
    #[ArrayShape( [
        'field'           => 'string',
        'localDomain'     => 'int',
        'localIdentifier' => 'string|null',
        'node'            => 'string|null',
        'clockSeq'        => 'int|null',
    ] )]
    protected function getListenerArgs(): array {
        return [
            'field'           => $this->field,
            'localDomain'     => $this->localDomain,
            'localIdentifier' => $this->localIdentifier instanceof IntegerObject
                ? (string) $this->localIdentifier
                : $this->localIdentifier,
            'node'            => $this->node instanceof Hexadecimal ? (string) $this->node : $this->node,
            'clockSeq'        => $this->clockSeq,
        ];
    }
    
}
