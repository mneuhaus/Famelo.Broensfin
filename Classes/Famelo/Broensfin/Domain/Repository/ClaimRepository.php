<?php
namespace Famelo\Broensfin\Domain\Repository;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class ClaimRepository extends \TYPO3\Flow\Persistence\Repository {
	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	public function createCreditorQuery() {
		$query = $this->createQuery();
		$user = $this->securityContext->getParty();
		$query->matching($query->equals('creditor', $user));
		return $query;
	}

	public function createDebtorQuery() {
		$query = $this->createQuery();
		$user = $this->securityContext->getParty();
		$query->matching($query->equals('debtor', $user));
		return $query;
	}
}
?>