<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Type;


use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Utils\Utils;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Swift\Serializer\Json;

class JsonType extends \GraphQL\Type\Definition\CustomScalarType {
    
    public const         NAME        = 'Json';
    private const        DESCRIPTION = 'The `Json` scalar type represents a Json object';
    
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    public $name = self::NAME;
    
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string|null
     */
    public $description = self::DESCRIPTION;
    
    public function __construct() {
        parent::__construct(
            [
                'name'        => self::NAME,
                'description' => self::DESCRIPTION,
            ]
        );
    }
    
    public function serialize( mixed $value ): string {
        if ( ! $value instanceof \stdClass ) {
            throw new InvariantViolation(
                'Json is not an instance of: ' . UuidInterface::class . ' ' . Utils::printSafe( $value )
            );
        }
        
        return ( new Json( $value ) )->serialize();
    }
    
    public function parseValue( mixed $value ): \stdClass {
        if ( ! is_string( $value ) ) {
            throw new InvalidArgumentException();
        }
        
        return ( new Json( $value ) )->modeObject()->unSerialize();
    }
    
    /** @param mixed[]|null $variables */
    public function parseLiteral( Node $valueNode, ?array $variables = null ): ?\stdClass {
        if ( ! $valueNode instanceof StringValueNode ) {
            return null;
        }
        
        return $this->parseValue( $valueNode->value );
    }
    
    
}