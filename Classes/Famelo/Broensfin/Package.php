<?php
namespace Famelo\Broensfin;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The TYPO3 Flow Package
 *
 */
class Package extends BasePackage {

	/**
	 * @var boolean
	 */
	protected $protected = TRUE;

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		$dispatcher->connect(
			'Famelo\Broensfin\Domain\Model\Claim',
			'created',
			'Famelo\Broensfin\Domain\Handler\ClaimHandler',
			'created'
		);
		$dispatcher->connect(
			'Famelo\Broensfin\Domain\Model\Claim',
			'stateUpdated',
			'Famelo\Broensfin\Domain\Handler\ClaimHandler',
			'stateUpdated'
		);
		$dispatcher->connect(
			'Famelo\Broensfin\Controller\Claim\DetailController',
			'claimCommentAdded',
			'Famelo\Broensfin\Domain\Handler\ClaimHandler',
			'commentAdded'
		);
	}
}
