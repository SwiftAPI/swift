<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Attributes;

use Attribute;
use Swift\Kernel\Attributes\DI;
use Swift\Security\Authorization\AuthorizationTypesEnum;

/**
 * Class Query
 * @package Swift\GraphQl\Attributes
 */
#[Attribute(Attribute::TARGET_METHOD), DI(exclude: true)]
class Query {

    /**
     * Query constructor.
     *
     * @param string|null $name
     * @param mixed $type
     * @param bool $nullable
     * @param bool $isList
     * @param string|null $generator
     * @param array $generatorArguments
     * @param string|null $description
     * @param AuthorizationTypesEnum[]|string[] $authTypes
     * @param string[] $isGranted
     */
    public function __construct(
        public string|null $name = null,
        public string|null $type = null,
        public bool $nullable = true,
        public bool $isList = false,
        public string|null $generator = null,
        public array $generatorArguments = array(),
        public string|null $description = null,
        public array $authTypes = [],
        public array $isGranted = [],
    ) {
        $authorizationToEnums = [];
        foreach ($authTypes as $authType) {
            $authorizationToEnums[] = new AuthorizationTypesEnum($authType);
        }
        $this->authTypes = $authorizationToEnums;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string {
        return $this->type;
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