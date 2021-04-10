<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Types;


use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use stdClass;
use Swift\Kernel\Attributes\DI;

/**
 * Class StdClassType
 * @package Swift\GraphQl\Types
 */
#[DI(autowire: false)]
class StdClassType extends ScalarType {
    /** @var string */
    public $name = Type::STDCLASS;

    /** @var string */
    public $description =
        'The `StdClass` scalar type represents object data, represented as UTF-8
character sequences. The StdClass type is most often used by GraphQL to
represent dynamic data.';

    /**
     * @param mixed $value
     *
     * @return stdClass
     */
    public function serialize( mixed $value ): stdClass {
        return (object) $value;
    }

    /**
     * @param mixed $value
     *
     * @return stdClass
     */
    public function parseValue( mixed $value ): stdClass {
        return (object) $value;
    }

    /**
     * @param Node $valueNode
     * @param mixed[]|null $variables
     *
     * @return string
     * @throws Error
     */
    public function parseLiteral( Node $valueNode, ?array $variables = null ) {
        $fields = $valueNode->fields;

        if ( empty( $fields ) ) {
            throw new Error();
        }

        $literal = new stdClass();
        foreach ( $fields as $field ) {
            $literal->{$field->name->value} = $field->value->value;
        }

        return $literal;
    }
}
