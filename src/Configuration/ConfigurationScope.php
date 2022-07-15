<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;

use Swift\Configuration\Tree\TreeInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ConfigurationScope
 * @package Swift\Configuration
 */
abstract class ConfigurationScope {

    protected TreeInterface $runtimeConfig;
    protected TreeInterface|null $appConfig;
    protected bool $appConfigHasModified = false;

    /**
     * @inheritDoc
     */
    public function get( string $name, string $scope ): mixed {
        return $this->runtimeConfig->get($name);
    }

    /**
     * @inheritDoc
     */
    public function set( mixed $value, string $name ): void {
        // Runtime config
        $this->runtimeConfig->set($name, $value);

        // Application specific stored config
        if ($this->appConfig->has($name)) {
            $this->appConfigHasModified = true;
            $this->appConfig->set($name, $value);
        }
    }

    public function has( string $identifier ): bool {
        return $this->runtimeConfig->has($identifier);
    }

    /**
     * Write updated app config
     */
    public function persist(): void {
        $filename = $this->appFilePath . DIRECTORY_SEPARATOR . $this->filename;
    
        if ($this->runtimeConfig && $this->appConfigHasModified && (new Filesystem())->exists($filename)) {
            file_put_contents($filename, $this->yaml->dump($this->runtimeConfig->toArray(), 2));
        }
    }

}