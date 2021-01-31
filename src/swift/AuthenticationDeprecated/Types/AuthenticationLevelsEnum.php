<?php


namespace Swift\AuthenticationDeprecated\Types;

use Swift\Kernel\TypeSystem\Enum;

class AuthenticationLevelsEnum extends Enum {

    public const NONE = 'none';
    public const APIKEY = 'apikey';
    public const TOKEN = 'token';
    public const LOGIN = 'login';

}