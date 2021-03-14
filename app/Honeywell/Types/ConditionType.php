<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Honeywell\Types;

use JetBrains\PhpStorm\Pure;
use stdClass;
use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\InputType;
use Swift\GraphQl\Attributes\Type;
use Swift\GraphQl\Types\DateTimeType;
use Swift\Kernel\Attributes\DI;

/**
 * Class ConditionType
 * @package Honeywell\Types
 */
#[DI( autowire: false ), Type]
class ConditionType {

    /**
     * ConditionType constructor.
     *
     * @param int|null $id
     * @param string $title
     * @param string $type
     * @param float $temp
     * @param stdClass $rules
     * @param \DateTime|null $created
     * @param \DateTime|null $modified
     * @param int $weight
     * @param int $state
     */
    #[Pure] public function __construct(
        #[Field( type: 'id' )] public ?int $id,
        #[Field] public string $title,
        #[Field( type: ConditionTypeEnum::class )] public string $type,
        #[Field] public float $temp,
        #[Field] public stdClass $rules,
        #[Field( type: \Swift\GraphQl\Types\Type::DATETIME, nullable: true )] public ?\DateTime $created = null,
        #[Field( type: \Swift\GraphQl\Types\Type::DATETIME, nullable: true )] public ?\DateTime $modified = null,
        #[Field( nullable: true )] public int $weight = 0,
        #[Field( nullable: true )] public int $state = 0,
    ) {
        $this->type     = ( new ConditionTypeEnum( $this->type ) )->getValue();
        $this->created  ??= date( 'Y-m-d H:i:s' );
        $this->modified ??= date( 'Y-m-d H:i:s' );
    }
}