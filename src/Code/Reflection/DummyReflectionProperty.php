<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Code\Reflection;

/**
 * Dummy reflection to use in cacheable runtimes
 */
class DummyReflectionProperty {
    
    public function __construct(
        private readonly string $name,
        private readonly string $declaringClass,
    ) {
    }
    
    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * @return string
     */
    public function getDeclaringClass(): string {
        return $this->declaringClass;
    }
    
}