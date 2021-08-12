<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Serializer;

/**
 * Interface SerializerInterface
 * @package Swift\Serializer
 */
interface SerializerInterface {

    public function serialize( mixed $value ): string;

    public function unSerialize( string $value ): mixed;

}