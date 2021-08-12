<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Mapping;


use JetBrains\PhpStorm\Pure;
use Swift\HttpFoundation\ParameterBag;
use Swift\Kernel\Attributes\DI;

/**
 * Class ClassMetaDataCacheBag
 * @package Swift\Model\Mapping
 */
#[DI(autowire: true, exclude: false)]
class ClassMetaDataCacheBag extends ParameterBag {

    /** @var ClassMetaData[] */
    protected array $parameters;

    /**
     * Returns a parameter by name.
     *
     * @param string $key
     * @param mixed $default The default value if the parameter key does not exist
     *
     * @return ClassMetaData
     */
    #[Pure]
    public function get( string $key, $default = null ): ClassMetaData {
        return \array_key_exists( $key, $this->parameters ) ? $this->parameters[ $key ] : $default;
    }

}