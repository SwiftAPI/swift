<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Entity;

use Dibi\Fluent;
use InvalidArgumentException;
use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\InputType;
use Swift\GraphQl\Attributes\Type;
use Swift\GraphQl\Generators\EntityEnumGenerator;
use Swift\Kernel\Attributes\DI;
use Swift\Model\Types\ArgumentDirectionEnum;
use TypeError;

/**
 * Class Arguments
 * @package Swift\Model\Entity
 */
#[InputType]
class Arguments extends \Swift\Model\Arguments\Arguments {


}