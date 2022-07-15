<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Utils;


final class Environment {
    
    public static function getCurrentEnvironment(): SwiftEnvironment {
        if (self::isCgi()) {
            return SwiftEnvironment::CGI;
        }
        if (self::isCli()) {
            return SwiftEnvironment::CLI;
        }
        if (self::isRuntime()) {
            return SwiftEnvironment::RUNTIME;
        }
        
        throw new \RuntimeException('Could not determine current Swift Environment');
    }
    
    public static function isCgi(): bool {
        return PHP_SAPI !== "cli";
    }
    
    public static function isCli(): bool {
        return PHP_SAPI === "cli";
    }
    
    public static function isRuntime(): bool {
        return defined( 'SWIFT_RUNTIME' ) && SWIFT_RUNTIME;
    }
    
}