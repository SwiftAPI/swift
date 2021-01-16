<?php declare(strict_types=1);

namespace Swift\Kernel\ContainerService;

use Swift\Kernel\ContainerService\CompilerPass\DependencyInjectionCompilerPass;
use Swift\Kernel\ContainerService\CompilerPass\EventRegistrationCompilerPass;
use Swift\Kernel\ContainerService\CompilerPass\ExtensionsCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Swift\Events\EventDispatcher;

class ContainerService extends ContainerBuilder {

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
	public function compile(bool $resolveEnvPlaceholders = false): void
	{
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
	public function getDefinitionsByTag(string $tag) : array {
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


}