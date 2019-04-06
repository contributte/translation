<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation;


/**
 * @author Ales Wita
 */
class Exception extends \Exception
{
}


/**
 * @author Ales Wita
 */
class InvalidArgumentException extends Exception
{
}


/**
 * @author Ales Wita
 */
class FileNotFoundException extends Exception
{
}


/**
 * @author Ales Wita
 */
class InvalidStateException extends Exception
{
}
