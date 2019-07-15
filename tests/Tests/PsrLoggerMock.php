<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Tests;

use Psr;

class PsrLoggerMock extends Psr\Log\AbstractLogger
{

	/**
	 * @inheritDoc
	 */
	public function log($level, $message, array $context = [])
	{
	}

}
