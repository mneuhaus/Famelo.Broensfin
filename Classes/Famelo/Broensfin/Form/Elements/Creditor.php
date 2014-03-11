<?php
namespace Famelo\Broensfin\Form\Elements;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Expose\Form\Elements\OptionsFormElement;
use TYPO3\Flow\Annotations as Flow;

/**
 * Custom form Element
 */
class Creditor extends OptionsFormElement {

	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	public function onSubmit(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime, &$elementValue) {
		$elementValue = $this->securityContext->getParty()->getTeam();
		// do something with the elementValue
	}

}