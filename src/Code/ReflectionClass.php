<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Code;

/**
 * Class ReflectionClass
 * @package Swift\Code
 */
class ReflectionClass extends \ReflectionClass {

    private \Closure $attributeReader;

    public function getParsedAttributes(): array {
        return $this->getAttributeReader()->getClassAnnotations($this);
    }

    public function getParsedAttribute( string $attribute ) {
        return $this->getAttributeReader()->getClassAnnotation($this, $attribute);
    }

    public function setAttributeReader( \Closure $proxy ): void {
        $this->attributeReader = $proxy;
    }

    public function getAttributeReader(): AttributeReader {
        $attributeReader = $this->attributeReader;
        return $attributeReader();
    }

}