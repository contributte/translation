<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tracy;

use Nette;
use Symfony;
use Tracy;
use Translette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Panel implements Tracy\IBarPanel
{
	use Nette\SmartObject;

	/** @var Translette\Translation\Translator */
	private $translator;

	/** @var array */
	private $resources = [];

	/** @var int */
	private $resourcesCount = 0;

	/** @var array */
	private $ignoredResources = [];

	/** @var int */
	private $ignoredResourcesCount = 0;

	/** @var array|null */
	private $localesWhitelist;


	/**
	 * @return string|null
	 */
	public function getTab(): ?string
	{
		return 'Translette';
	}


	/**
	 * @return string|null
	 */
	public function getPanel(): ?string
	{
		$panel = [];


		// resources
		if (count($this->resources) > 0) {
			ksort($this->resources);

			if (count($panel) !== 0) {
				$panel[] = '<br><br>';
			}

			$panel[] = '<h1>Loaded resources: '. $this->resourcesCount .'</h1>';
			$panel[] = self::createResourcePanelHelper($this->resources);
		}


		// ignored resources
		if (count($this->ignoredResources) > 0) {
			ksort($this->ignoredResources);

			if (count($panel) !== 0) {
				$panel[] = '<br><br>';
			}

			$panel[] = '<h1>Ignored resources: '. $this->ignoredResourcesCount .', <small>whitelist: ' . implode(', ', array_map('htmlspecialchars', $this->localesWhitelist)) . '</small></h1>';
			$panel[] = self::createResourcePanelHelper($this->ignoredResources);
		}


		return count($panel) === 0 ? null : implode($panel);
	}



	/**
	 * @internal
	 *
	 * @param array $resources
	 * @return string
	 */
	private static function createResourcePanelHelper(array $resources): string
	{
		$string = '';

		foreach ($resources as $k1 => $v1) {
			foreach ($v1 as $k2 => $v2) {
				$string .= '<tr>';
				$string .= '<td>' . htmlspecialchars($k1) . '</td>';
				$string .= '<td>' . htmlspecialchars($v2) . '</td>';
				$string .= '<td>' . htmlspecialchars(dirname($k2)) . '/<strong>' . htmlspecialchars(basename($k2)) . '</strong></td>';
				$string .= '</tr>';
			}
		}

		return '<table style="width: 100%"><tr><th>Locale</th><th>Domain</th><th>Resource file name</th></tr>' . $string . '</table>';
	}



	/**
	 * @param string|null $format
	 * @param string|array|null $resource
	 * @param string|null $locale
	 * @param string|null $domain
	 * @return self
	 */
	public function addResource(?string $format, $resource, ?string $locale, ?string $domain): self
	{
		if (is_array($resource)) {
			$resource = 'array ' . md5(serialize($resource));
		}

		$this->resources[$locale][$resource] = $domain;
		$this->resourcesCount++;
		return $this;
	}


	/**
	 * @param string|null $format
	 * @param string|array|null $resource
	 * @param string|null $locale
	 * @param string|null $domain
	 * @return self
	 */
	public function addIgnoredResource(?string $format, $resource, ?string $locale, ?string $domain): self
	{
		if (is_array($resource)) {
			$resource = 'array ' . md5(serialize($resource));
		}

		$this->ignoredResources[$locale][$resource] = $domain;
		$this->ignoredResourcesCount++;
		return $this;
	}


	/**
	 * @param array|null $whitelist
	 * @return self
	 */
	public function setLocalesWhitelist(?array $whitelist): self
	{
		$this->localesWhitelist = $whitelist;
		return $this;
	}
}
