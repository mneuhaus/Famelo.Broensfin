<?php
namespace Famelo\Broensfin\Controller;

use Famelo\Saas\Domain\Model\Transaction;
use Omnipay\Common\CreditCard;
use Omnipay\Omnipay;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;
use TYPO3\Flow\Utility\Algorithms;

/**
 * @Flow\Scope("singleton")
 */
class ClaimOverviewController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var \Famelo\Saas\Domain\Service\TransactionService
	 * @Flow\Inject
	 */
	protected $transactionService;

	/**
	 * @var \Famelo\Saas\Services\SaasService
	 * @Flow\Inject
	 */
	protected $saasService;

	/**
	 * @var \Famelo\Broensfin\Domain\Repository\ClaimRepository
	 * @Flow\Inject
	 */
	protected $claimRepository;

	/**
	 * @return string
	 */
	public function indexAction() {
		$stats = array(
			'debts' => array(),
			'claims' => array()
		);

		foreach ($this->claimRepository->createCreditorQuery()->execute() as $claim) {
			$status = $claim->getCurrentState()->__toString();
			if (!isset($stats['debts'][$status])) {
				$stats['debts'][$status] = 0;
			}
			$stats['debts'][$status]++;
		}

		foreach ($this->claimRepository->createDebtorQuery()->execute() as $claim) {
			$status = $claim->getCurrentState()->__toString();
			if (!isset($stats['claims'][$status])) {
				$stats['claims'][$status] = 0;
			}
			$stats['claims'][$status]++;
		}

		$this->view->assign('stats', $stats);
		$this->view->assign('user', $this->transactionService->getUser());
		$this->view->assign('claims', $this->claimRepository->findClaimsThatInvolveMe());
	}
}

?>
