<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User;

use JetBrains\PhpStorm\Pure;
use Swift\HttpFoundation\Exception\BadRequestException;
use Swift\Security\Authorization\AuthorizationRole;

/**
 * Class UserRolesBag
 * @package Swift\Security\User
 */
class UserRolesBag {
    
    /**
     * Parameter storage.
     */
    protected array $parameters;
    
    /**
     * @param AuthorizationRole[]|string[] $parameters
     */
    public function __construct( array $parameters = [] ) {
        $this->parameters = $this->castValues($parameters);
    }
    
    /**
     * Returns the parameters.
     *
     * @return AuthorizationRole[] An array of parameters
     */
    public function all(/*string $key = null*/ ): array {
        $key = \func_num_args() > 0 ? func_get_arg( 0 ) : null;
        
        if ( null === $key ) {
            return $this->parameters;
        }
        
        if ( ! \is_array( $value = $this->parameters[ $key ] ?? [] ) ) {
            throw new BadRequestException( sprintf( 'Unexpected value for parameter "%s": expecting "array", got "%s".', $key, get_debug_type( $value ) ) );
        }
        
        return $value;
    }
    
    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     */
    #[Pure]
    public function keys(): array {
        return array_keys( $this->parameters );
    }
    
    /**
     * Replaces the current parameters by a new set.
     *
     * @param AuthorizationRole[]|string[] $parameters
     */
    public function replace( array $parameters = [] ): void {
        $this->parameters = $this->castValues($parameters);
    }
    
    /**
     * Adds parameters.
     *
     * @param AuthorizationRole[] $parameters
     */
    public function add( array $parameters = [] ): void {
        $this->parameters = array_replace( $this->parameters, $this->castValues($parameters) );
    }
    
    /**
     * Sets a parameter by name.
     *
     * @param \Swift\Security\Authorization\AuthorizationRole|string $role
     */
    public function set( AuthorizationRole|string $role ): void {
        $this->parameters[ $role->value ] = $this->castValue($role);
    }
    
    /**
     * Returns true if the parameter is defined.
     *
     * @param \Swift\Security\Authorization\AuthorizationRole|string $key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    #[Pure]
    public function has( AuthorizationRole|string $key ): bool {
        return \array_key_exists( $this->castValue($key), $this->parameters );
    }
    
    /**
     * Removes a parameter.
     *
     * @param \Swift\Security\Authorization\AuthorizationRole|string $key
     */
    public function remove( AuthorizationRole|string $key ): void {
        unset( $this->parameters[ $this->castValue($key) ] );
    }
    
    /**
     * Returns a parameter by name.
     *
     * @param \Swift\Security\Authorization\AuthorizationRole|string      $key
     * @param \Swift\Security\Authorization\AuthorizationRole|string|null $default The default value if the parameter key does not exist
     *
     * @return \Swift\Security\Authorization\AuthorizationRole|null
     */
    #[Pure]
    public function get( AuthorizationRole|string $key, AuthorizationRole|string|null $default = null ): ?AuthorizationRole {
        return \array_key_exists( $this->castValue($key), $this->parameters ) ? $this->parameters[ $key->value ] : $default;
    }
    
    /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator( $this->parameters );
    }
    
    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    #[Pure]
    public function count(): int {
        return \count( $this->parameters );
    }
    
    private function castValues( array $parameters ): array {
        return array_map( static fn( AuthorizationRole|string $role): string => is_string($role) ? $role : $role->value, $parameters);
    }
    
    private function castValue( AuthorizationRole|string $role ): string {
        return is_string($role) ? $role : $role->value;
    }
    
}