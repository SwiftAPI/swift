<?php

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Application\Bootstrap;

use Swift\Kernel\Attributes\DI;

#[DI(exclude: true)]
class Functions {}

/**
 * Recursively search multidimensional array by list of keys
 */
if (!function_exists('array_recursive_key')) {
    function array_recursive_key( array $array, ...$keys ): mixed {
        if (empty($keys)) {
            throw new \InvalidArgumentException('Provide at least one key to search');
        }

        $searchKey = array_key_first($keys);

        if (!is_array($array)) {
            throw new \TypeError(sprintf('Recursive array search can not search on non-array. Trying to find %s on %s', $keys[$searchKey], $array));
        }

        if (!array_key_exists($keys[$searchKey], $array)) {
            throw new \TypeError(sprintf('Key %s does not exist in given array', $keys[$searchKey]));
        }

        $array = $array[$keys[$searchKey]];
        unset($keys[$searchKey]);

        if (!empty($keys)) {
            $array = array_recursive_key($array, ...$keys);
        }

        return $array;
    }
}

/**
 * Recursively update multidimensional array by list of keys
 */
if (!function_exists('array_recursive_update')) {
    function array_recursive_update( array $array, mixed $value, ...$keys ): array {
        if (empty($keys)) {
            throw new \InvalidArgumentException('Provide at least one key to search');
        }

        $searchKey = array_key_first($keys);

        if (!is_array($array)) {
            throw new \TypeError(sprintf('Recursive array search can not search on non-array. Trying to find %s on %s', $keys[$searchKey], $array));
        }

        if (!array_key_exists($keys[$searchKey], $array)) {
            throw new \TypeError(sprintf('Key %s does not exist in given array', $keys[$searchKey]));
        }

        $array = $array[$keys[$searchKey]];
        unset($keys[$searchKey]);

        if (!empty($keys)) {
            $array = array_recursive_update($array, $value, ...$keys);
        } else {
            $array = $value;
        }

        return $array;
    }
}