<?php
namespace Famelo\Broensfin\Neos;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Domain\Service\ConfigurationContentDimensionPresetSource;
use TYPO3\Neos\Domain\Service\ContentContextFactory;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;

/**
 * A TypoScript Language Menu object
 */
class LanguageImplementation extends AbstractTypoScriptObject {

	/**
	 * @Flow\Inject
	 * @var ConfigurationContentDimensionPresetSource
	 */
	protected $configurationContentDimensionPresetSource;

	/**
	 * @Flow\Inject
	 * @var ContentContextFactory
	 */
	protected $contextFactory;

	/**
	 * An internal cache for the built menu items array.
	 * @var array
	 */
	protected $items;

	/**
	 * An internal cache for the current locacle.
	 * @var array
	 */
	protected $currentLocale;

	/**
	 * @var NodeInterface
	 */
	protected $currentNode;

	/**
	 * @var \TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository
	 * @Flow\Inject
	 */
	protected $nodeDataRepository;

	/**
	 * @var \TYPO3\Flow\Persistence\Doctrine\PersistenceManager
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * Returns the menu items according to the defined settings.
	 *
	 * @return array
	 */
	public function getItems() {
		if ($this->items === NULL) {
			$this->items = $this->buildItems();
		}
		return $this->items;
	}

	/**
	 * Returns the menu items according to the defined settings.
	 *
	 * @return array
	 */
	public function getCurrentLocale() {
		if ($this->currentLocale === NULL) {
			$currentContext = $this->tsRuntime->getCurrentContext();
			if (!isset($currentContext['node'])) {
				throw new TypoScriptException('You must set a "node" in the TypoScript context.', 1391689525);
			}
			$this->currentNode = $currentContext['node'];

			$dimensions = $this->currentNode->getContext()->getDimensions();
			$currentLocale = $this->configurationContentDimensionPresetSource->findPresetByDimensionValues('locales', $dimensions['locales']);
			$this->currentLocale = $currentLocale['identifier'];
		}

		return $this->currentLocale;
	}

	/**
	 * Just return the processed value
	 *
	 * @return mixed
	 */
	public function evaluate() {
		return $this->getCurrentLocale();
	}

}