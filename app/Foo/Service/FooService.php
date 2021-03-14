<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Foo\Service;

use Swift\Configuration\ConfigurationInterface;
use Swift\HttpFoundation\RequestInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Model\EntityInterface;
use Swift\Security\Security;

/**
 * Class FooService
 * @package Foo\Service
 */
#[DI(tags: ['foo.service', 'foo.example']), Autowire]
class FooService {

    private RequestInterface $request;
    private iterable $services;

    /**
     * FooService constructor.
     *
     * @param Security $security
     * @param ConfigurationInterface $configuration
     * @param EntityInterface $fooRepository
     * @param string|null $nonAutowired
     */
    public function __construct(
        private Security $security,
        private ConfigurationInterface $configuration,
        private EntityInterface $fooRepository,
        private string|null $nonAutowired = null,
    ) {
    }

    #[Autowire]
    public function setAutowired( RequestInterface $request ): void {
        $this->request = $request;
    }

    #[Autowire]
    public function setTaggedServices( #[Autowire(tag: 'example_tag')] iterable $services ): void {
        $this->services = $services;
    }

}