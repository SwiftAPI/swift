<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Behavior\Uuid;

use Cycle\Schema\Registry;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Swift\Orm\Behavior\BaseModifier;
use Swift\Orm\Behavior\RegistryModifier;

abstract class Uuid extends BaseModifier {
    
    protected ?string $column = null;
    protected string $field;
    
    public function compute( Registry $registry ): void {
        $modifier     = new RegistryModifier( $registry, $this->role );
        $this->column = $modifier->findColumnName( $this->field, $this->column );
        
        if ( $this->column !== null ) {
            $modifier->addUuidColumn( $this->column, $this->field );
            $modifier->setTypecast(
                $registry->getEntity( $this->role )->getFields()->get( $this->field ),
                [ RamseyUuid::class, 'fromString' ]
            );
        }
    }
    
    public function render( Registry $registry ): void {
        $modifier     = new RegistryModifier( $registry, $this->role );
        $this->column = $modifier->findColumnName( $this->field, $this->column ) ?? $this->field;
        
        $modifier->addUuidColumn( $this->column, $this->field );
        $modifier->setTypecast(
            $registry->getEntity( $this->role )->getFields()->get( $this->field ),
            [ RamseyUuid::class, 'fromString' ]
        );
    }
    
}
