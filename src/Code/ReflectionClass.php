<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Code;

use phpDocumentor\Reflection\Types\Context;

/**
 * Class ReflectionClass
 * @package Swift\Code
 */
class ReflectionClass extends \ReflectionClass {

    private \Closure $attributeReader;
    private \Closure $docBlockFactory;

    public function getParsedAttributes(): array {
        return $this->getAttributeReader()->getClassAnnotations($this);
    }

    public function getParsedAttribute( string $attribute ) {
        return $this->getAttributeReader()->getClassAnnotation($this, $attribute);
    }

    public function getContext(): Context {
        return $this->getDocBlockFactory()->getContextFromClass($this);
    }

    public function setAttributeReader( \Closure $proxy ): void {
        $this->attributeReader = $proxy;
    }

    public function getAttributeReader(): AttributeReader {
        $attributeReader = $this->attributeReader;
        return $attributeReader();
    }

    public function setDocBlockFactory( \Closure $proxy ): void {
        $this->docBlockFactory = $proxy;
    }

    public function getDocBlockFactory(): DocBlockFactory {
        $docBlockFactory = $this->docBlockFactory;
        return $docBlockFactory();
    }

}