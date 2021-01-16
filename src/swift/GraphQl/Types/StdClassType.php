<?php declare(strict_types=1);


namespace Swift\GraphQl\Types;


use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use stdClass;

class StdClassType extends ScalarType
{
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
    public function serialize(mixed $value): stdClass
    {
        return (object) $value;
    }

    /**
     * @param mixed $value
     *
     * @return stdClass
     */
    public function parseValue(mixed $value): stdClass
    {
        return (object) $value;
    }

    /**
     * @param Node $valueNode
     * @param mixed[]|null $variables
     *
     * @return string
     * @throws Error
     */
    public function parseLiteral(Node $valueNode, ?array $variables = null)
    {
        $fields = $valueNode->fields;

        if (empty($fields)) {
            throw new Error();
        }

        $literal = new stdClass();
        foreach ($fields as $field) {
            $literal->{$field->name->value} = $field->value->value;
        }

        return $literal;
    }
}
