<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Logging\Formatter;

/**
 * Class LineFormatter
 * @package Swift\Logging\Formatter
 */
class LineFormatter extends \Monolog\Formatter\LineFormatter {

    /**
     * LineFormatter constructor.
     *
     * @param string|null $format
     * @param string|null $dateFormat
     * @param bool $allowInlineLineBreaks
     * @param bool $ignoreEmptyContextAndExtra
     */
    public function __construct( ?string $format = null, ?string $dateFormat = null, bool $allowInlineLineBreaks = false, bool $ignoreEmptyContextAndExtra = false ) {
        $dateFormat = is_null($dateFormat) ? 'Y-m-d H:i:s' : $dateFormat;
        $format = is_null($format) ? "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n" : $format;

        parent::__construct( $format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra );
    }

}