<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\AuthenticationDeprecated\Helper;

use JetBrains\PhpStorm\Pure;

/**
 * Class Token
 * @package Swift\AuthenticationDeprecated\Helper
 */
class Token {

	/**
	 * Method to get unique token
	 *
	 * @param string $base
	 * @param int    $maxLenght
	 *
	 * @return string
	 */
	#[Pure] public function generateUniqueToken( string $base = null, int $maxLenght = 0) : string {
		$base   = $base ? $base : strtotime('now');
		$token  = md5(base64_encode($base . uniqid() . strtotime('now')));

		return $maxLenght ? substr($token, 0, $maxLenght) : $token;
	}

}