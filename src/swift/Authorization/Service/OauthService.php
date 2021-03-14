<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Authorization\Service;

use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\Server;
use OAuth2\Storage\Pdo;
use Swift\Configuration\Configuration;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class OauthService
 * @package Swift\Authorization\Service
 */
#[Autowire]
class OauthService {

    private Pdo $storage;
    public Server $server;

    /**
     * OauthService constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(
        private Configuration $configuration,
    ) {
        $prefix = $this->configuration->get('connection.prefix', 'database');
        $this->storage = new Pdo(array(
            'dsn' => sprintf('mysql:dbname=%s;host=%s',
                //$this->configuration->get('database.driver', 'root'),
                $this->configuration->get('connection.database', 'database'),
                $this->configuration->get('connection.host', 'database')
            ),
            'username' => $this->configuration->get('connection.username', 'database'),
            'password' =>     $this->configuration->get('connection.password', 'database'),
        ), array(
            'client_table' => $prefix . 'oauth_clients',
            'access_token_table' => $prefix . 'oauth_access_tokens',
            'refresh_token_table' => $prefix . 'oauth_refresh_tokens',
            'code_table' => $prefix . 'oauth_authorization_codes',
            'user_table' => $prefix . 'users',
            'jwt_table'  => $prefix . 'oauth_jwt',
            'jti_table'  => $prefix . 'oauth_jti',
            'scope_table'  => $prefix . 'oauth_scopes',
            'public_key_table'  => $prefix . 'oauth_public_keys',
        ));
        $this->server = new Server($this->storage);
        $this->server->addGrantType(new ClientCredentials($this->storage));
        $this->server->addGrantType(new AuthorizationCode($this->storage));
    }

}