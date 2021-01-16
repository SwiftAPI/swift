<?php declare(strict_types=1);


namespace Swift\Logging\Formatter;


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