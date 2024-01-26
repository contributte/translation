<?php declare(strict_types = 1);

namespace Tests;

use Psr\Log\AbstractLogger;

final class PsrLoggerMock extends AbstractLogger
{

	/**
	 * @inheritDoc
	 */
	public function log(
		$level,
		$message,
		array $context = []
	): void
	{
	}

}
