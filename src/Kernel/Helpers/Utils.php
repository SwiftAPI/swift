<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Helpers;

/**
 * Class Utils
 * @package Swift\Kernel\Helpers
 */
class Utils {

    /**
     * @param string $fqn
     *
     * @return string
     */
    public static function classFqnToAliasVariable( string $fqn ): string {
        $fqn = str_replace(search: 'Swift\\', replace: '', subject: $fqn);

        $fragments = explode(separator: '\\', string: $fqn);
        $variable = '';

        foreach ($fragments as $key => $fragment) {
            $variable .= ($key > 1) ? ucfirst($fragment) : lcfirst($fragment);
        }

        return $variable;
    }

}