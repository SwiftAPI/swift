<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\ORM\Mapping\Driver;

/**
 * Class AttributeDriver
 * @package Swift\ORM\Mapping\Driver
 */
class AttributeDriver extends \Doctrine\ORM\Mapping\Driver\AttributeDriver {

    /**
     * AttributeDriver constructor.
     */
    public function __construct( array $paths ) {
        $this->reader = new AttributeReader();
        if ( ! $paths ) {
            return;
        }

        $this->addPaths( $paths );
    }

}