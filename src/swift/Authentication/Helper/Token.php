<?php declare(strict_types=1);

namespace Swift\Authentication\Helper;

use JetBrains\PhpStorm\Pure;

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