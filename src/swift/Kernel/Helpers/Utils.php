<?php declare(strict_types=1);


namespace Swift\Kernel\Helpers;


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