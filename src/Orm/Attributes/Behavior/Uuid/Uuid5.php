<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Behavior\Uuid;

use Swift\Orm\Behavior\Listener\Uuid\Uuid5 as Listener;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\UuidInterface;

/**
 * Uses a version 5 (name-based) UUID based on the SHA-1 hash of a
 * namespace ID and a name
 */
#[\Attribute( \Attribute::TARGET_CLASS ), NamedArgumentConstructor]
#[\AllowDynamicProperties]
final class Uuid5 extends Uuid {
    
    /**
     * @param non-empty-string|UuidInterface $namespace The namespace (must be a valid UUID)
     * @param non-empty-string               $name      The name to use for creating a UUID
     * @param non-empty-string               $field     Uuid property name
     * @param non-empty-string|null          $column    Uuid column name
     *
     * @see \Ramsey\Uuid\UuidFactoryInterface::uuid5()
     */
    public function __construct(
        private readonly string|UuidInterface $namespace,
        private readonly string               $name,
        string                                $field = 'uuid',
        ?string                               $column = null
    ) {
        $this->field  = $field;
        $this->column = $column;
    }
    
    protected function getListenerClass(): string {
        return Listener::class;
    }
    
    #[ArrayShape( [ 'field' => 'string', 'namespace' => 'string', 'name' => 'string' ] )]
    protected function getListenerArgs(): array {
        return [
            'field'     => $this->field,
            'namespace' => $this->namespace instanceof UuidInterface ? (string) $this->namespace : $this->namespace,
            'name'      => $this->name,
        ];
    }
}
