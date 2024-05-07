<?php declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\DependencyInjection\LazyProxy\PhpDumper;

use Symfony\Component\DependencyInjection\Definition;

/**
 * Patched version of the Symfony NullDumper.
 *
 * Null dumper, negates any proxy code generation for any given service definition.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 *
 * @final
 */
class NullDumper implements DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function isProxyCandidate(Definition $definition): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getProxyFactoryCode(Definition $definition, string $id, string $factoryCode): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getProxyCode(Definition $definition): string
    {
        return '';
    }
}
