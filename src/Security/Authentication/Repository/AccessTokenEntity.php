<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Repository;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Swift\Model\Exceptions\InvalidStateException;
use Swift\Model\Result;
use stdClass;

/**
 * Class AccessTokenEntity
 * @package Swift\Security\Authentication\Repository
 */
#[Entity, Table(name: 'security_access_tokens')]
final class AccessTokenEntity {

    #[Id, GeneratedValue, Column(name: 'id', type: 'integer', length: 11)]
    private int $id;

    #[Column( name: 'access_token', type: 'string', length: 40, unique: true, nullable: false )]
    private string $accessToken;

    #[Column(name: 'client_id', type: 'integer', length: 11, nullable: true)]
    private int $clientId;

    #[Column(name: 'user_id', type: 'integer', length: 11, nullable: true)]
    private ?int $userId;

    #[Column(name: 'expires', type: 'datetime', nullable: false)]
    private DateTime $expires;

    #[Column(name: 'scope', type: 'text', length: 4000, nullable: true)]
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
    }
}