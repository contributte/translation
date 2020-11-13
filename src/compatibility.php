<?php declare(strict_types = 1);

namespace Contributte\Translation;

if (false) {

	/** @deprecated use Contributte\Translation\LoggerTranslator */
	class LoggerTranslator
	{

	}

} elseif (!class_exists(LoggerTranslator::class)) {
	class_alias(Translator::class, LoggerTranslator::class);
}

if (false) {
	
	/** @deprecated use Contributte\Translation\DebuggerTranslator */
	class DebuggerTranslator
	{

	}

} elseif (!class_exists(DebuggerTranslator::class)) {
	class_alias(Translator::class, DebuggerTranslator::class);
}
