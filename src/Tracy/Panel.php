<?php declare(strict_types = 1);

namespace Contributte\Translation\Tracy;

use Contributte\Translation\LocalesResolvers\ResolverInterface;
use Contributte\Translation\Translator;
use ReflectionClass;
use Tracy\IBarPanel;

class Panel implements IBarPanel
{

	private Translator $translator;

	/** @var array<array{id: string, domain: string, count: int}> */
	private array $missingTranslation = [];

	private int $missingTranslationCount = 0;

	/** @var array<\Contributte\Translation\LocalesResolvers\ResolverInterface> */
	private array $localeResolvers = [];

	/** @var array<string, array<string, string>> */
	private array $resources = [];

	private int $resourcesCount = 0;

	/** @var array<string, array<string, string>> */
	private array $ignoredResources = [];

	private int $ignoredResourcesCount = 0;

	public function __construct(
		Translator $translator
	)
	{
		$this->translator = $translator;
		$translator->setTracyPanel($this);
	}

	public function getTab(): string
	{
		// https://postimg.cc/jC4Nq2wg
		$icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"><g fill-rule="evenodd"><path d="M135 72v23H54l-2 1v13l1 13 68 1h68c1 2 0 10-2 16a98 98 0 0 0-2 6v2l-1 1-1 3c-3 8-7 15-12 21s-14 15-16 15a82 82 0 0 1-16-17l-3-4-3-5-10-17-2-4H94v1l8 19 4 5c5 9 21 29 30 38 2 2 2 2 0 4a570 570 0 0 1-59 40c-4 3-5 2 3 13s6 11 13 6l5-2a345 345 0 0 0 16-10 480 480 0 0 0 39-26c3-2 2-3 7 1a300 300 0 0 0 47 34c6 3 6 2 5 4a273 273 0 0 1-11 30l-1 2-1 2a977 977 0 0 1-9 26h24v-1l1-2a128 128 0 0 1 5-13l2-5a339 339 0 0 0 11-31h70l1 3a653 653 0 0 0 17 48l1 1h25l-4-13a174 174 0 0 1-6-17l-2-5a1344 1344 0 0 0-30-82 38 38 0 0 1-2-5l-2-5v-1l-1-2-2-6v-2h-1v-2l-1-2-1-3-1-3a454 454 0 0 1-11-29l-27 1-1 3a402 402 0 0 1-3 8l-1 3a168 168 0 0 1-5 12 201 201 0 0 0-7 19 157 157 0 0 1-6 15l-1 2v2a94 94 0 0 0-5 11l-1 5c-4 10-4 9-7 7a202 202 0 0 1-42-33l2-3a120 120 0 0 0 19-25 176 176 0 0 0 20-54h13l13-1V96l-39-1h-39V71l-31 1m135 110a733 733 0 0 1 21 58l4 14-27 1c-30 0-28 0-27-3a635 635 0 0 0 23-61v-3l1-2a52 52 0 0 1 3-7v-1l2 4" fill="#f9fafb"/><path d="m14 14-2 2v368l2 2 2 2h368l2-2 2-2V16l-2-2-2-2H16l-2 2m152 57v24h39l39 1v26l-13 1h-13l-2 5-1 8-1 3v2l-2 5a176 176 0 0 1-24 45 120 120 0 0 1-11 14 119 119 0 0 0 23 21l19 12c3 2 3 3 7-7a122 122 0 0 1 3-10 94 94 0 0 1 4-10 71 71 0 0 0 3-6v-1l1-2a157 157 0 0 0 6-16 201 201 0 0 1 8-21l1-3a1041 1041 0 0 1 4-11 230 230 0 0 1 28 0v2l1 1v2l2 4 1 3a318 318 0 0 1 13 36v1l2 5 1 2 1 3a115 115 0 0 1 4 11 126 126 0 0 1 2 6 104 104 0 0 1 4 11 100 100 0 0 1 4 12 401 401 0 0 0 9 22 1344 1344 0 0 1 19 55h-25l-1-1-2-4a417 417 0 0 0-15-44l-1-3h-70l-1 3a39 39 0 0 1-2 4v2l-1 2-1 1v1l-1 2-2 6a339 339 0 0 1-11 30v1h-24l4-11a206 206 0 0 0 7-19 348 348 0 0 0 11-30c1-2 1-1-5-4a195 195 0 0 1-36-25l-11-9c-5-4-4-3-7-1l-2 1-2 1-1 2-2 1a480 480 0 0 1-53 33c-7 5-5 5-13-6s-7-10-3-13a1939 1939 0 0 0 59-40c2-2 2-2 0-4a318 318 0 0 1-34-43l-8-19v-1h27l2 4a137 137 0 0 0 32 43c2 0 11-9 16-15a98 98 0 0 0 16-33c2-6 3-14 2-16h-68l-68-1-1-13V96l2-1h81V72l31-1m102 107v1l-1 2-2 5-1 2v3l-1 1-1 3-2 5a62 62 0 0 1-2 7l-2 3a163 163 0 0 0-5 16l-3 8-7 18c-1 3-3 3 27 3l27-1-4-14a321 321 0 0 1-11-30 700 700 0 0 0-12-32" fill="#3464ac"/></g></svg>';
		$errMsg = ($this->missingTranslationCount > 1) ? 'errors' : 'error';

		return '<span title="Contributte/Translation">' .
			$icon .
			'&nbsp;<strong>' .
			$this->translator->getLocale() .
			'</strong>' .
			($this->missingTranslationCount > 0 ? ' (' . $this->missingTranslationCount . ' ' . $errMsg . ')' : '') .
			'</span>';
	}

	public function getPanel(): string
	{
		$panel = [];

		$panel[] = '<h1>Missing translations: ' . $this->missingTranslationCount . ', Loaded resources: ' . $this->resourcesCount . '</h1>';
		$panel[] = '<div class="tracy-inner">';

		$panel[] = '<div class="tracy-inner-container"><h2>Translator settings</h2>';
		$panel[] = '<table class="tracy-sortable"><colgroup><col style="width: 75%"><col style="width: 25%"></colgroup>';
		$panel[] = '<tr><th>Name</th><th>Value</th></tr>';
		$panel[] = '<tr><td>Default locale</td><td class="contributte-translation-default-locale">' . htmlspecialchars($this->translator->getDefaultLocale()) . '</td></tr>';
		$panel[] = '<tr><td>Locales whitelist</td><td class="contributte-translation-locales-whitelist">' . ($this->translator->getLocalesWhitelist() !== null ? htmlspecialchars(implode(', ', $this->translator->getLocalesWhitelist())) : '&nbsp;') . '</td></tr>';
		$panel[] = '</table></div>';

		// missing translations
		if ($this->missingTranslationCount > 0) {
			$panel[] = '<br>';
			$panel[] = '<div class="tracy-inner-container"><h2>Missing translations: ' . $this->missingTranslationCount . '</h2>';

			$panel[] = '<table class="tracy-sortable"><colgroup><col style="width: 50%"><col style="width: 25%"><col style="width: 25%"></colgroup>';
			$panel[] = '<tr><th>ID</th><th>Domain</th><th>Count</th></tr>';

			foreach ($this->missingTranslation as $v1) {
				$panel[] = '<tr class="contributte-translation-missing-translation"><td>' . htmlspecialchars($v1['id']) . '</td><td>' . htmlspecialchars($v1['domain']) . '</td><td>' . $v1['count'] . '</td></tr>';
			}

			$panel[] = '</table></div>';
		}

		// locale resolvers
		if (count($this->localeResolvers) > 0) {
			$panel[] = '<br>';
			$panel[] = '<div class="tracy-inner-container"><h2>Locale resolvers</h2>';
			$panel[] = '<table class="tracy-sortable"><colgroup><col style="width: 5%"><col style="width: 70%"><col style="width: 25%"></colgroup>';
			$panel[] = '<tr><th>#</th><th>Resolver</th><th>Locale</th></tr>';

			$counter = 1;
			foreach ($this->localeResolvers as $v1) {
				$reflection = new ReflectionClass($v1);
				$locale = $v1->resolve($this->translator);

				$panel[] = '<tr class="contributte-translation-locale-resolvers"><td>' . $counter++ . '.</td><td title="' . $reflection->getName() . '">' . $reflection->getShortName() . '</td><td>' . ($locale !== null ? htmlspecialchars($locale) : '<i>n/a</i>') . '</td></tr>';
			}

			$panel[] = '</table></div>';
		}

		// resources
		if (count($this->resources) > 0) {
			ksort($this->resources);

			$panel[] = '<br>';
			$panel[] = '<div class="tracy-inner-container"><h2>Loaded resources: ' . $this->resourcesCount . '</h2>';
			$panel[] = self::createResourcePanelHelper($this->resources, 'contributte-translation-resources');
			$panel[] = '</div>';
		}

		// ignored resources
		if (count($this->ignoredResources) > 0) {
			ksort($this->ignoredResources);

			$panel[] = '<br>';
			$panel[] = '<h2>Ignored resources: ' . $this->ignoredResourcesCount . ($this->translator->getLocalesWhitelist() !== null ? ', <small>whitelist: ' . implode(', ', array_map('htmlspecialchars', $this->translator->getLocalesWhitelist())) . '</small>' : null) . '</h2>';
			$panel[] = self::createResourcePanelHelper($this->ignoredResources, 'contributte-translation-ignored-resources');
		}

		$panel[] = '</div>';

		return implode('', $panel);
	}

	public function addMissingTranslation(
		string $id,
		string $domain
	): self
	{
		$key = $domain . '.' . $id;

		if (!array_key_exists($key, $this->missingTranslation)) {
			$this->missingTranslation[$key] = [
				'id' => $id,
				'domain' => $domain,
				'count' => 0,
			];

			$this->missingTranslationCount++;
		}

		$this->missingTranslation[$key]['count']++;

		return $this;
	}

	public function addLocaleResolver(
		ResolverInterface $resolver
	): self
	{
		$this->localeResolvers[] = $resolver;

		return $this;
	}

	public function addResource(
		string $resource,
		string $locale,
		string $domain
	): self
	{
		$this->resources[$locale][$resource] = $domain;
		$this->resourcesCount++;

		return $this;
	}

	/**
	 * @return array<array<string>>
	 * @internal
	 */
	public function getResources(): array
	{
		return $this->resources;
	}

	public function addIgnoredResource(
		string $resource,
		string $locale,
		string $domain
	): self
	{
		$this->ignoredResources[$locale][$resource] = $domain;
		$this->ignoredResourcesCount++;

		return $this;
	}

	/**
	 * @return array<string, array<string, string>>
	 * @internal
	 */
	public function getIgnoredResources(): array
	{
		return $this->ignoredResources;
	}

	/**
	 * @param array<array<string>> $resources
	 */
	private static function createResourcePanelHelper(
		array $resources,
		string $class
	): string
	{
		$string = '<table class="tracy-sortable"><colgroup><col style="width: 10%"><col style="width: 10%"><col style="width: 80%"></colgroup>';
		$string .= '<tr><th>Locale</th><th>Domain</th><th>File name</th></tr>';

		foreach ($resources as $k1 => $v1) {
			foreach ($v1 as $k2 => $v2) {
				$string .= '<tr class="' . $class . '">';
				$string .= '<td>' . htmlspecialchars($k1) . '</td>';
				$string .= '<td>' . htmlspecialchars($v2) . '</td>';
				$string .= '<td>' . htmlspecialchars(dirname($k2)) . '/<strong>' . htmlspecialchars(basename($k2)) . '</strong></td>';
				$string .= '</tr>';
			}
		}

		return $string . '</table>';
	}

}
