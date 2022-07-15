<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Builder;


use Swift\GraphQl\Exception\InvalidArgument;

abstract class TypeBuilder {
    
    public const VALID_NAME_PATTERN = '~^[_a-zA-Z][_a-zA-Z0-9]*$~';
    
    protected string $name;
    
    protected ?string $description = null;
    
    protected function __construct( string $name ) {
        if ( preg_match( self::VALID_NAME_PATTERN, $name ) !== 1 ) {
            throw InvalidArgument::invalidNameFormat( $name );
        }
        
        $this->name = $name;
    }
    
    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * @return $this
     */
    public function setDescription( string $description ): self {
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * @return array<string, mixed>
     */
    public function build(): mixed {
        return [
            'name'        => $this->name,
            'description' => $this->description,
        ];
    }
    
    abstract public function buildType(): mixed;
    
}