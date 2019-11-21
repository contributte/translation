<?php declare(strict_types = 1);

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
	 * @param string|null $id
	 * @param mixed[] $parameters
	 * @param string|null $domain
	 * @param string|null $locale
	 * @return string
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
	 */
	public function trans($id, array $parameters = [], $domain = null, $locale = null)
	{
		if ($id !== null && $this->tracyPanel !== null) {
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
