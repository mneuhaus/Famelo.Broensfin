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
		$team = $this->securityContext->getParty()->getTeam();
		$query->matching($query->equals('creditor', $team));
		return $query;
	}

	public function createDebtorQuery() {
		$query = $this->createQuery();
		$team = $this->securityContext->getParty()->getTeam();
		$query->matching($query->equals('debtor', $team));
		return $query;
	}

	public function findClaimsThatInvolveMe() {
		$query = $this->createQuery();
		$team = $this->securityContext->getParty()->getTeam();
		$query->matching($query->logicalOr(
			$query->equals('creditor', $team),
			$query->equals('debtor', $team)
		));
		$query->setOrderings(array('created' => 'DESC'));
		return $query->execute();
	}
}
?>