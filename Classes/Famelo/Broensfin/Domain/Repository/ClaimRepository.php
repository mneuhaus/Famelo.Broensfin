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

	public function findByCsv($row) {
		$query = $this->createQuery();

		$constraints = array();

		$identifiers = array(
			'creditor.name' => 'Creditor',
			'creditor.street' => 'Street',
			'creditor.city' => 'City',
			'creditor.zip' => 'Zip',
			'externalReference' => 'Reference'
		);

		foreach ($identifiers as $internal => $external) {
			if (!empty($row[$external])) {
				$constraints[] = $query->equals($internal, $row[$external]);
			}
		}

		$query->matching($query->logicalAnd($constraints));

		return $query->execute();
	}
}
?>