<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Container;

use Swift\Kernel\Container\CompilerPass\DependencyInjectionCompilerPass;
use Swift\Kernel\Container\CompilerPass\EventRegistrationCompilerPass;
use Swift\Kernel\Container\CompilerPass\ExtensionsCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Swift\Events\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Container
 * @package Swift\Kernel\Container
 */
class Container extends ContainerBuilder {

	/**
	 * Compiles the container.
	 *
	 * This method passes the container to compiler
	 * passes whose job is to manipulate and optimize
	 * the container.
	 *
	 * The main compiler passes roughly do four things:
	 *
	 *  * The extension configurations are merged;
	 *  * Parameter values are resolved;
	 *  * The parameter bag is frozen;
	 *  * Extension loading is disabled.
	 *
	 * @param bool $resolveEnvPlaceholders Whether %env()% parameters should be resolved using the current
	 *                                     env vars or be replaced by uniquely identifiable placeholders.
	 *                                     Set to "true" when you want to use the current ContainerBuilder
	 *                                     directly, keep to "false" when the container is dumped instead.
	 */
	public function compile(bool $resolveEnvPlaceholders = false): void {
	    // Support deprecated usage of container global. Usage is highly discouraged. Use injection and compiler passes instead.
	    global $container;
	    $container = $this;

        // Register event dispatcher for dependency injection (that's why it's set to public)
        $this->register(EventDispatcher::class, EventDispatcher::class)->setPublic(true);

        $this->addCompilerPass(new DependencyInjectionCompilerPass());
        $this->addCompilerPass(new ExtensionsCompilerPass());

        parent::compile();


        // Post compile
        /** @var CompilerPassInterface[] $post_compile */
        $post_compile = array(new EventRegistrationCompilerPass());
		foreach ($post_compile as $compilerPass) {
		    $compilerPass->process($this);
        }
	}

	/**
	 * Method to get classes by tag
	 *
	 * @param string $tag
	 *
	 * @return array
	 */
	public function getServicesByTag(string $tag): array {
		$definitions = array();

		if (empty($this->getDefinitions())) {
			return $definitions;
		}

		$tag = strtolower($tag);
		foreach ($this->getDefinitions() as $key => $definition) {
			if ($definition->hasTag($tag)) {
				$definitions[] = $key;
			}
		}

		return $definitions;
	}

    /**
     * Get all service instances for given tag
     *
     * @param string $tag
     *
     * @return array
     * @throws \Exception
     */
    public function getServiceInstancesByTag( string $tag ): array {
        $definitions = array();

        if (empty($this->getDefinitions())) {
            return $definitions;
        }

        $tag = strtolower($tag);
        foreach ($this->getDefinitions() as $key => $definition) {
            if ($definition->hasTag($tag)) {
                $definitions[] = $this->get($key);
            }
        }

        return $definitions;
	}


}