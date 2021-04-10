<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl;

/**
 * Class Utils
 * @package Swift\GraphQl
 */
class Utils {

    /**
     * Encode string by Typename and id
     *
     * @param string $type
     * @param string|int $id
     *
     * @return string
     */
    public static function encodeId( string $type, string|int $id ): string {
        return base64_encode($type . ':' . $id);
    }

    /**
     * Decode id to array
     *
     * @param string $id
     *
     * @return array
     */
    public static function decodeId( string $id ): array {
        $decoded = base64_decode($id, true) ?? throw new \TypeError('Cannot decode id, as it not a valid base64 alphabet');
        $exploded = explode(':', $decoded);

        return array(
            'type' => $exploded[0] ?? null,
            'id' => $exploded[1] ?? null,
        );
    }

    /**
     * Encode cursor
     *
     * @param string|int $id
     *
     * @return string
     */
    public static function encodeCursor( string|int $id ) {
        return base64_encode('arrayconnection:' . $id);
    }

    /**
     * Decode cursor
     *
     * @param string $id
     *
     * @return string|int
     */
    public static function decodeCursor( string $id ): string|int {
        $decoded = base64_decode($id, true) ?? throw new \TypeError('Cannot decode id, as it not a valid base64 alphabet');
        $exploded = explode(':', $decoded);
        return end($exploded);
    }

}