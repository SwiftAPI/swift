<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection\Provider;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\KernelDiTags;

/**
 * Trait ControllerAwareTrait
 * @package Swift\DependencyInjection\Provider
 */
#[Autowire]
trait ControllersAwareTrait {

    protected array $controllers;

    #[Autowire]
    public function setControllers( #[Autowire(tag: KernelDiTags::CONTROLLER)] iterable $controllers ): void {
        $controllers = iterator_to_array($controllers);
        
        foreach ($controllers as $controller) {
            $this->controllers[$controller::class] = $controller;
        }
    }

}