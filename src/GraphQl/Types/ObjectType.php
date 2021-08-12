<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Types;

use Swift\Kernel\Attributes\DI;
use Swift\Security\Authorization\AuthorizationTypesEnum;

/**
 * Class ObjectType
 * @package Swift\GraphQl\Types
 */
#[DI(exclude: true)]
class ObjectType {

    /**
     * ObjectType constructor.
     *
     * @param string $name
     * @param string $declaringClass
     * @param string|null $declaringMethod
     * @param array $fields
     * @param string|null $resolve
     * @param array|null $args
     * @param string|array|null $type
     * @param mixed|null $defaultValue
     * @param bool $nullable
     * @param bool $isList
     * @param string|null $generator
     * @param array $generatorArguments
     * @param array $interfaces
     * @param string|null $description
     * @param AuthorizationTypesEnum[] $authTypes
     * @param string[] $isGranted
     */
    public function __construct(
        public string $name,
        public string $declaringClass,
        public string|null $declaringMethod = null,
        public array $fields = array(),
        public string|null $resolve = null,
        public array|null $args = null,
        public string|array|null $type = null,
        public mixed $defaultValue = null,
        public bool $nullable = true,
        public bool $isList = false,
        public string|null $generator = null,
        public array $generatorArguments = array(),
        public array $interfaces = array(),
        public string|null $description = null,
        public array $authTypes = [],
        public array $isGranted = [],
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDeclaringClass(): string {
        return $this->declaringClass;
    }

    /**
     * @return string|null
     */
    public function getDeclaringMethod(): ?string {
        return $this->declaringMethod;
    }

    /**
     * @return array
     */
    public function getFields(): array {
        return $this->fields;
    }

    /**
     * @return string|null
     */
    public function getResolve(): ?string {
        return $this->resolve;
    }

    /**
     * @return array|null
     */
    public function getArgs(): ?array {
        return $this->args;
    }

    /**
     * @return array|string|null
     */
    public function getType(): array|string|null {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue(): mixed {
        return $this->defaultValue;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool {
        return $this->nullable;
    }

    /**
     * @return bool
     */
    public function isList(): bool {
        return $this->isList;
    }

    /**
     * @return string|null
     */
    public function getGenerator(): ?string {
        return $this->generator;
    }

    /**
     * @return array
     */
    public function getGeneratorArguments(): array {
        return $this->generatorArguments;
    }

    /**
     * @return array
     */
    public function getInterfaces(): array {
        return $this->interfaces;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string {
        return $this->description;
    }

    /**
     * @return AuthorizationTypesEnum[]
     */
    public function getAuthTypes(): array {
        return $this->authTypes;
    }

    /**
     * @return string[]
     */
    public function getIsGranted(): array {
        return $this->isGranted;
    }



}