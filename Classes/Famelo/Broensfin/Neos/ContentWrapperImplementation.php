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
use TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface;
use TYPO3\Neos\Domain\Service\ContentContext;
use TYPO3\Neos\Service\ContentElementWrappingService;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;
use TYPO3\Neos\Domain\Exception;

/**
 * Adds meta data attributes to the processed Content Element
 */
class ContentWrapperImplementation extends AbstractTypoScriptObject {
	/**
	 * The string to be processed
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->tsValue('value');
	}

	/**
	 * Evaluate this TypoScript object and return the result
	 *
	 * @return mixed
	 */
	public function evaluate() {
		$content = $this->getValue();
		$node = $this->tsValue('node');
		if ($node->isRemoved()) {
			return '';
		}
		if ($node->getDepth() !== 4) {
			return $content;
		}
		$wrapperClass = 'wrapper';
		if ($node->hasProperty('wrapper')) {
			$wrapperClass = $node->getProperty('wrapper');
		}
		$content = '<div class="' . $wrapperClass . '"><div class="container">' . $content . '</div></div>';
		return $content;
	}

}
