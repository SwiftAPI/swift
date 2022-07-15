<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;


class Utils {
    
    /**
     * Determine whether config has either dev mode or debugging enabled
     *
     * @param \Swift\Configuration\ConfigurationInterface $configuration
     *
     * @return bool
     */
    public static function isDevModeOrDebug( ConfigurationInterface $configuration ): bool {
        return ($configuration->get('app.mode', 'root') === 'develop') || $configuration->get('app.debug', 'root');
    }
    
    /**
     * Determine whether application develop mode is enabled
     *
     * @param \Swift\Configuration\ConfigurationInterface $configuration
     *
     * @return bool
     */
    public static function isDevMode( ConfigurationInterface $configuration ): bool {
        return $configuration->get('app.mode', 'root') === 'develop';
    }
    
    /**
     * Determine whether application is running in production mode
     *
     * @param \Swift\Configuration\ConfigurationInterface $configuration
     *
     * @return bool
     */
    public static function isProductionMode( ConfigurationInterface $configuration ): bool {
        return $configuration->get('app.mode', 'root') === 'production';
    }
    
    /**
     * Determine whether debugging mode is enabled
     *
     * @param \Swift\Configuration\ConfigurationInterface $configuration
     *
     * @return bool
     */
    public static function isDebug( ConfigurationInterface $configuration ): bool {
        return $configuration->get('app.debug', 'root');
    }
    
    /**
     * Determine whether cache should be utilized
     *
     * @param \Swift\Configuration\ConfigurationInterface $configuration
     *
     * @return bool
     */
    public static function isCacheEnabled( ConfigurationInterface $configuration ): bool {
        return !self::isDevModeOrDebug( $configuration );
    }
    
}