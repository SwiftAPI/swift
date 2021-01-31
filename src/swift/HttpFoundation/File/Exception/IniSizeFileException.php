<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\File\Exception;

/**
 * Thrown when an UPLOAD_ERR_INI_SIZE error occurred with UploadedFile.
 *
 * @author Florent Mata <florentmata@gmail.com>
 */
class IniSizeFileException extends FileException
{
}
