<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model;

use Closure;
use stdClass;
use Swift\Kernel\Attributes\DI;

/**
 * Class Result
 * @package Swift\Model
 */
#[DI(autowire: false)]
final class Result extends stdClass {

    /**
     * Result constructor.
     *
     * @param Closure $entityReference
     */
    public function __construct(
        private Closure $entityReference,
    ) {
    }

    public function __serialize(): array {
        $values = array();

        foreach ($this->getEntity()->getPropertyMap() as $name => $value) {
            if (property_exists($this, $name)) {
                $values[$name] = $this->{$name};
            }
        }
        foreach ($this->getEntity()->getJoinsMap() as $name => $join) {
            $values[$name] = $this->{$name};
        }

        return $values;
    }

    /**
     * Serialize to array
     *
     * @return array
     */
    public function toArray(): array {
        return $this->__serialize();
    }

    /**
     * Serialize to object
     *
     * @return stdClass
     */
    public function toObject(): stdClass {
        return (object) $this->__serialize();
    }

    /**
     * @return mixed
     */
    public function getPrimaryKeyValue(): mixed {
        return $this->{$this->getEntity()->getPrimaryKey()};
    }

    public function __call( string $name, array $arguments ): mixed {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return null;
    }

    /**
     * Get entity by callback
     * 
     * @return EntityInterface
     */
    private function getEntity(): EntityInterface {
        $ref = $this->entityReference;
        
        return $ref();
    }

    public function __set( string $name, mixed $value ): void {
        $this->{$name} = $value;
    }

    public function __get( $name ): mixed {
        return $this->{$name} ?? null;
    }

    public function __isset( $name ): bool {
        return isset($this->{$name});
    }

    public function __unset( $name ): void {
        if (isset($name->{$name})) {
            unset($this->{$name});
        }
    }

}