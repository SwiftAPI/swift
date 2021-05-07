<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security;

/**
 * Class Utils
 * @package Swift\Security
 */
class Utils {

    /**
     * Generate random token
     *
     * @return string
     * @throws \Exception
     */
    public static function randomToken(): string {
        if (function_exists('random_bytes')) {
            $randomData = random_bytes(20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $randomData    = openssl_random_pseudo_bytes(20 );
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        if (function_exists('mcrypt_create_iv')) {
            $randomData = mcrypt_create_iv(20, MCRYPT_DEV_RANDOM);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        if (@file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        // Last resort which you probably should just get rid of:
        $randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);

        return substr(hash('sha512', $randomData), 0, 40);
    }

}