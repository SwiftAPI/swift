<?php declare(strict_types=1);

namespace Swift\Application\Bootstrap\Autoloading;

require_once INCLUDE_DIR . '/vendor/autoload.php';

class Autoloader {

    /**
     * Register autoloaders
     */
    public function initialize(): void {
        spl_autoload_register( static function( $className) {
            $classPathLc = lcfirst(str_replace("\\", DIRECTORY_SEPARATOR, $className) . '.php');
            $classPathUc = ucfirst(str_replace("\\", DIRECTORY_SEPARATOR, $className) . '.php');

            // Check if class exists
            if (file_exists(INCLUDE_DIR . '/vendor/' . $classPathLc)) {
                include_once INCLUDE_DIR . '/vendor/' . $classPathLc;
            }
            if (file_exists(INCLUDE_DIR . '/vendor/' . $classPathUc)) {
                include_once INCLUDE_DIR . '/vendor/' . $classPathUc;
            }
            if (file_exists(INCLUDE_DIR . '/src/' . $classPathLc)) {
                include_once INCLUDE_DIR . '/src/' . $classPathLc;
            }
            if (file_exists(INCLUDE_DIR . '/src/' . $classPathUc)) {
                include_once INCLUDE_DIR . '/src/' . $classPathUc;
            }
            if (file_exists(INCLUDE_DIR . '/app/' . $classPathLc)) {
                include_once INCLUDE_DIR . '/app/' . $classPathLc;
            }
            if (file_exists(INCLUDE_DIR . '/app/' . $classPathUc)) {
                include_once INCLUDE_DIR . '/app/' . $classPathUc;
            }
        });
    }
    
}