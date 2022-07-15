<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Exception;


use GraphQL\Utils\Utils;
use Swift\GraphQl\Schema\Builder\TypeBuilder;
use Throwable;

class InvalidArgument extends \InvalidArgumentException implements \GraphQL\Error\ClientAware {
    
    final protected function __construct( string $message = '', int $code = 0, ?Throwable $previous = null ) {
        parent::__construct( $message, $code, $previous );
    }
    
    public static function invalidNameFormat( string $invalidName ): self {
        return new self( sprintf( 'Name "%s" does not match pattern "%s"', $invalidName, TypeBuilder::VALID_NAME_PATTERN ) );
    }
    
    public static function valueNotIso8601Compliant( mixed $invalidValue ): self {
        return new self(
            sprintf(
                'DateTime type expects input value to be ISO 8601 compliant. Given invalid value "%s"',
                Utils::printSafeJson( $invalidValue ),
            )
        );
    }
    
    /**
     * @inheritDoc
     */
    public function isClientSafe(): bool {
        return true;
    }
    
    /**
     * @inheritDoc
     */
    public function getCategory(): string {
        return '';
    }
    
}