<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Container\Provider;

use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\KernelDiTags;

/**
 * Trait ControllerAwareTrait
 * @package Swift\Kernel\Container\Provider
 */
#[Autowire]
trait ControllersAwareTrait {

    private array $controllers;

    #[Autowire]
    public function setControllers( #[Autowire(tag: KernelDiTags::CONTROLLER)] iterable $controllers ): void {
        foreach ($controllers as $controller) {
            $this->controllers[$controller::class] = $controller;
        }
    }

}