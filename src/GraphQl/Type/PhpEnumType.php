<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Type;


use Doctrine\Inflector\InflectorFactory;
use Exception;
use GraphQL\Error\SerializationError;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Utils\PhpDoc;
use GraphQL\Utils\Utils;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionEnum;
use UnitEnum;

/**
 * @phpstan-import-type PartialEnumValueConfig from EnumType
 */
class PhpEnumType extends EnumType {
    
    public const MULTIPLE_DESCRIPTIONS_DISALLOWED = 'Using more than 1 Description attribute is not supported.';
    public const MULTIPLE_DEPRECATIONS_DISALLOWED = 'Using more than 1 Deprecated attribute is not supported.';
    
    /**
     * @var class-string<UnitEnum>
     */
    protected string $enumClass;
    
    /**
     * @param class-string<UnitEnum> $enum
     */
    public function __construct( string $enum ) {
        $this->enumClass = $enum;
        $reflection      = new ReflectionEnum( $enum );
        
        /**
         * @var array<string, PartialEnumValueConfig> $enumDefinitions
         */
        $enumDefinitions = [];
        foreach ( $reflection->getCases() as $case ) {
            $enumDefinitions[ $case->name ] = [
                'value' => $case->getValue(),
            ];
        }
        
        parent::__construct(
            [
                'name'   => $this->baseName( $enum ),
                'values' => $enumDefinitions,
            ]
        );
    }
    
    public function serialize( $value ): string {
        if ( ! is_a( $value, $this->enumClass ) ) {
            $notEnum = Utils::printSafe( $value );
            
            throw new \RuntimeException( "Cannot serialize value as enum: {$notEnum}, expected instance of {$this->enumClass}." );
        }
        
        return $value->name;
    }
    
    /**
     * @param class-string $class
     */
    public static function baseName( string $class ): string {
        $inflection = InflectorFactory::create()->build();
        $parts      = explode( '\\', $class );
        
        return $inflection->capitalize( end( $parts ) );
    }
    
    
}