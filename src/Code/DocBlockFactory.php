<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Code;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory as PhpDocBlockFactory;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Swift\Cache\CacheFactory;
use Swift\Kernel\Attributes\Autowire;
use Webmozart\Assert\Assert;

use function filemtime;
use function md5;

/**
 * Class DocBlockFactory
 * @package Swift\Code
 */
#[Autowire]
final class DocBlockFactory {

    public const CACHE_NAME = 'code/docblock.php';

    /** @var CacheInterface */
    private CacheInterface $cache;

    /** @var PhpDocBlockFactory */
    private PhpDocBlockFactory $docBlockFactory;

    /** @var array<string, DocBlock> */
    private array $docBlockArrayCache = [];

    /** @var array<string, Context> */
    private array $contextArrayCache = [];

    /** @var ContextFactory */
    private ContextFactory $contextFactory;

    /**
     * @param CacheFactory $cacheFactory
     */
    public function __construct(
        private CacheFactory $cacheFactory,
    ) {
        $this->cache           = $this->cacheFactory->create(self::CACHE_NAME);
        $this->docBlockFactory = PhpDocBlockFactory::createInstance();
        $this->contextFactory  = new ContextFactory();
    }

    /**
     * Fetches a DocBlock object from a ReflectionMethod
     */
    public function getMethodDocBlock( ReflectionMethod $refMethod ): DocBlock {
        $key = 'docblock_' . md5( $refMethod->getDeclaringClass()->getName() . '::' . $refMethod->getName() );
        if ( isset( $this->docBlockArrayCache[ $key ] ) ) {
            return $this->docBlockArrayCache[ $key ];
        }

        $fileName = $refMethod->getFileName();
        Assert::string( $fileName );

        $cacheItem = $this->cache->get( $key );
        if ( $cacheItem->isHit() ) {
            [
                'time'     => $time,
                'docblock' => $docBlock,
            ] = $cacheItem;

            if ( filemtime( $fileName ) === $time ) {
                $this->docBlockArrayCache[ $key ] = $docBlock;

                return $docBlock;
            }
        }

        $docBlock = $this->doGetDocBlock( $refMethod );

        $this->cache->set( $key, [
            'time'     => filemtime( $fileName ),
            'docblock' => $docBlock,
        ] );
        $this->docBlockArrayCache[ $key ] = $docBlock;

        return $docBlock;
    }

    /**
     * Fetches a DocBlock object from a ReflectionMethod
     */
    public function getPropertyDocBlock( ReflectionProperty $refProp ): DocBlock {
        $key = 'docblock_' . md5( $refProp->getDeclaringClass()->getName() . '::' . $refProp->getName() );
        if ( isset( $this->docBlockArrayCache[ $key ] ) ) {
            return $this->docBlockArrayCache[ $key ];
        }

        $fileName = $refProp->getDeclaringClass()->getFileName();
        Assert::string( $fileName );

        $cacheItem = $this->cache->get( $key );
        if ( $cacheItem !== null ) {
            [
                'time'     => $time,
                'docblock' => $docBlock,
            ] = $cacheItem;

            if ( filemtime( $fileName ) === $time ) {
                $this->docBlockArrayCache[ $key ] = $docBlock;

                return $docBlock;
            }
        }

        $docBlock = $this->doGetDocBlock( $refProp );

        $this->cache->set( $key, [
            'time'     => filemtime( $fileName ),
            'docblock' => $docBlock,
        ] );
        $this->docBlockArrayCache[ $key ] = $docBlock;

        return $docBlock;
    }

    private function doGetDocBlock( ReflectionMethod|ReflectionProperty $refMethod ): DocBlock {
        $docComment = $refMethod->getDocComment() ?: '/** */';

        $refClass     = $refMethod->getDeclaringClass();
        $refClassName = $refClass->getName();

        if ( ! isset( $this->contextArrayCache[ $refClassName ] ) ) {
            $this->contextArrayCache[ $refClassName ] = $this->contextFactory->createFromReflector( $refMethod );
        }

        return $this->docBlockFactory->create( $docComment, $this->contextArrayCache[ $refClassName ] );
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    public function getContextFromClass( ReflectionClass $reflectionClass ): Context {
        $className = $reflectionClass->getName();
        if ( isset( $this->contextArrayCache[ $className ] ) ) {
            return $this->contextArrayCache[ $className ];
        }

        $key = 'docblockcontext_' . md5( $className );

        $fileName = $reflectionClass->getFileName();
        Assert::string( $fileName );

        $cacheItem = $this->cache->get( $key );
        if ( $cacheItem->isHit() ) {
            [
                'time'    => $time,
                'context' => $context,
            ] = $cacheItem;

            if ( filemtime( $fileName ) === $time ) {
                $this->contextArrayCache[ $className ] = $context;

                return $context;
            }
        }

        $context = $this->contextFactory->createFromReflector( $reflectionClass );

        $this->cache->set( $key, [
            'time'    => filemtime( $fileName ),
            'context' => $context,
        ] );

        $this->contextArrayCache[ $className ] = $context;

        return $context;
    }

}