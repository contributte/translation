<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation;

use Contributte;

/**
 * @property      Contributte\Translation\Tracy\Panel|null $tracyPanel
 */
class DebuggerTranslator extends LoggerTranslator
{

	/** @var Contributte\Translation\Tracy\Panel|null */
	private $tracyPanel;

	public function getTracyPanel(): ?Tracy\Panel
	{
		return $this->tracyPanel;
	}

	public function setTracyPanel(?Tracy\Panel $tracyPanel): self
	{
		$this->tracyPanel = $tracyPanel;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function trans($id, array $parameters = [], $domain = null, $locale = null)
	{
		if ($this->tracyPanel !== null) {
			if ($domain === null) {
				$domain = 'messages';
			}

			if (!$this->getCatalogue()->has($id, $domain)) {
				$this->tracyPanel->addMissingTranslation($id, $domain);
			}
		}

		return parent::trans($id, $parameters, $domain, $locale);
	}

}
