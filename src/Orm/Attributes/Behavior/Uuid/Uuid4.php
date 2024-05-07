<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Behavior\Uuid;

use Swift\Orm\Behavior\Listener\Uuid\Uuid4 as Listener;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Uses a version 4 (random) UUID
 */
#[\Attribute( \Attribute::TARGET_CLASS ), NamedArgumentConstructor]
final class Uuid4 extends Uuid {
    
    /**
     * @param non-empty-string      $field  Uuid property name
     * @param non-empty-string|null $column Uuid column name
     *
     * @see \Ramsey\Uuid\UuidFactoryInterface::uuid4()
     */
    public function __construct(
        string  $field = 'uuid',
        ?string $column = null
    ) {
        $this->field  = $field;
        $this->column = $column;
    }
    
    protected function getListenerClass(): string {
        return Listener::class;
    }
    
    #[ArrayShape( [ 'field' => 'string' ] )]
    protected function getListenerArgs(): array {
        return [
            'field' => $this->field,
        ];
    }
    
}
