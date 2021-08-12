<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Code;

use SplObjectStorage;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class ReflectionFactory
 * @package Swift\Code
 */
#[Autowire]
final class ReflectionFactory {

    /**
     * @var ReflectionClass[] $objectStorage
     */
    private SplObjectStorage $objectStorage;

    /**
     * ReflectionFactory constructor.
     */
    public function __construct(
        private AttributeReader $attributeReader,
        //private DocBlockFactory $docBlockFactory,
    ) {
        $this->objectStorage = new SplObjectStorage();
    }

    /**
     * @param string|object $class
     *
     * @return ReflectionClass
     */
    public function getReflectionClass( string|object $class ): ReflectionClass {
        $class = is_object($class) ? $class::class : $class;

        $this->objectStorage->rewind();
        while ($this->objectStorage->valid()) {
            $object = $this->objectStorage->current(); // similar to current($s)
            $data = $this->objectStorage->getInfo();
            if ($class === $data) {
                $this->objectStorage->rewind();

                return $object;
            }
            $this->objectStorage->next();
        }

        $classReflector = $this->createReflection( $class );
        $this->objectStorage->attach($classReflector, $class);

        return $classReflector;
    }

    /**
     * @return AttributeReader
     */
    public function getAttributeReader(): AttributeReader {
        return $this->attributeReader;
    }

//    /**
//     * @return DocBlockFactory
//     */
//    public function getDocBlockFactory(): DocBlockFactory {
//        return $this->docBlockFactory;
//    }

    private function createReflection( string $class ): ReflectionClass {
        $classReflector = new ReflectionClass($class);

        $attributeReader = $this->attributeReader;
        //$docBlockFactory = $this->docBlockFactory;

        $classReflector->setAttributeReader(function () use ($attributeReader) {
            return $attributeReader;
        });
//        $classReflector->setDocBlockFactory(function () use ($docBlockFactory) {
//            return $docBlockFactory;
//        });

        return $classReflector;
    }

}