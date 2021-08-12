<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Entity;

use DateTime;
use Swift\Kernel\Attributes\DI;
use Swift\Model\Attributes\Field;
use Swift\Model\Attributes\Table;
use Swift\Model\Entity;
use Swift\Model\EntityInterface;
use Swift\Model\Exceptions\InvalidStateException;
use Swift\Model\Query\Result;
use Swift\Model\Types\FieldTypes;
use stdClass;

/**
 * Class AccessTokenEntity
 * @package Swift\Authorization\Model
 */
#[DI(aliases: [EntityInterface::class . ' $accessTokenEntity']), Table(name: 'security_access_tokens')]
final class AccessTokenEntity extends Entity {

    #[Field(name: 'id', primary: true, type: FieldTypes::INT, length: 11)]
    private int $id;

    #[Field( name: 'access_token', type: FieldTypes::TEXT, length: 40, empty: false, unique: true )]
    private string $accessToken;

    #[Field(name: 'client_id', type: FieldTypes::INT, length: 11, empty: true)]
    private int $clientId;

    #[Field(name: 'user_id', type: FieldTypes::INT, length: 11, empty: true)]
    private ?int $userId;

    #[Field(name: 'expires', type: FieldTypes::DATETIME, empty: false)]
    private DateTime $expires;

    #[Field(name: 'scope', type: FieldTypes::LONGTEXT, length: 4000, empty: true)]
    private ?string $scope;

    /**
     * Method to save/update based on the current state
     *
     * @param array|stdClass $state
     *
     * @return Result
     */
    public function save( array|stdClass $state ): Result {
        $state = (array) $state;

        if (empty($state['clientId']) && empty($state['userId']) && empty($state['id'])) {
            throw new InvalidStateException('Both clientId and userId are empty. At least one of those needs to be referenced to create a valid token.');
        }

        return parent::save($state);
    }
}