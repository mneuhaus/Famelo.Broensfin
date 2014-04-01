<?php
namespace Famelo\Broensfin\Domain\Factory;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Famelo\Broensfin\Domain\Model\Claim;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Account;

/**
 * A factory to conveniently create User models
 *
 */
class ClaimFactory {
	/**
	 * Creates a Team
	 *
	 * @return \TYPO3\Neos\Domain\Model\User The created user instance
	 */
	public function create() {
		$claim = new Claim();
		return $claim;
	}

	public function preSave($claim) {
	}

}
