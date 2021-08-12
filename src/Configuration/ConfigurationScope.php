<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;

use Swift\Configuration\Tree\TreeInterface;
use Swift\Events\Attribute\ListenTo;
use Swift\Events\EventListenerInterface;
use Swift\Kernel\Event\KernelOnBeforeShutdown;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ConfigurationScope
 * @package Swift\Configuration
 */
abstract class ConfigurationScope implements EventListenerInterface {

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
     * Write updated app config on kernel shutdown
     */
    #[ListenTo(event: KernelOnBeforeShutdown::class)]
    public function onKernelShutdown(): void {
        $filename = $this->appFilePath . DIRECTORY_SEPARATOR . $this->filename;
        if ($this->appConfig && $this->appConfigHasModified && (new Filesystem())->exists($filename)) {
            file_put_contents($filename, $this->yaml->dump($this->appConfig->toArray(), 2));
        }
    }

}