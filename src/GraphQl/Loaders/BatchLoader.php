<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Loaders;


use Swift\GraphQl\LoaderInterface;
use Swift\GraphQl\TypeRegistryInterface;

/**
 * Class BatchLoader
 * @package Swift\GraphQl\Loaders
 */
class BatchLoader implements LoaderInterface {

    /**
     * BatchLoader constructor.
     *
     * @param array $loaders
     */
    public function __construct(
        private array $loaders = array(),
    ) {
    }

    /**
     * @param LoaderInterface[] $loaders
     */
    public function setLoaders( array $loaders ): void {
        $this->loaders = array_merge($this->loaders, $loaders);
    }

    /**
     * Load types into given type registry
     *
     * @param TypeRegistryInterface $typeRegistry
     */
    public function load( TypeRegistryInterface $typeRegistry ): void {
        foreach ($this->loaders as $loader) {
            $loader->load($typeRegistry);
        }
    }
}