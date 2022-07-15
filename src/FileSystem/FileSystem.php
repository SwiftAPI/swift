<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem;

use League\Flysystem\PathNormalizer;

/**
 * Class FileSystem
 * @package Swift\FileSystem
 */
class FileSystem extends \League\Flysystem\Filesystem {

    /**
     * FileSystem constructor.
     */
    public function __construct(
        protected ?LocalFileAdapter $adapter = null,
        protected array $config = [],
        protected ?PathNormalizer $pathNormalizer = null
    ) {
        $adapter ??= new LocalFileAdapter(INCLUDE_DIR);
        $this->adapter = $adapter;
        parent::__construct(
            $adapter,
            $config,
            $pathNormalizer,
        );
    }

    public function exists( string $location ): bool {
        return $this->adapter->exists( $location );
    }

    public function dirExists( string $location ): bool {
        return $this->adapter->dirExists( $location );
    }

}