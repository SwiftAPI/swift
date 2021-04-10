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
 * Interface LoaderInterface
 * @package Swift\GraphQl
 */
interface LoaderInterface {

    /**
     * Load types into given type registry
     *
     * @param TypeRegistryInterface $typeRegistry
     */
    public function load( TypeRegistryInterface $typeRegistry ): void;

}