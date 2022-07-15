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

class FieldUnAuthorizedException extends \InvalidArgumentException implements \GraphQL\Error\ClientAware {
    
    final protected function __construct( string $message = '', int $code = 0, ?Throwable $previous = null ) {
        parent::__construct( $message, $code, $previous );
    }
    
    public static function fieldUnAuthorized( string $fieldName, string $parent ): self {
        return new self( sprintf( 'You are not authorized to query field "%s" on "%s"', $fieldName, $parent ) );
    }
    
    public static function queryOrMutationUnAuthorized( string $name ): self {
        return new self( sprintf( 'You are not authorized to query field "%s"', $name ) );
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