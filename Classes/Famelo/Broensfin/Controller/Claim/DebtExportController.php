<?php
namespace Famelo\Broensfin\Controller\Claim;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Expose".               *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use Famelo\Broensfin\Domain\Model\Claim;
use Famelo\Saas\Csv\Reader;
use Famelo\Saas\Domain\Model\Transaction;
use League\Csv\Writer;
use TYPO3\Expose\Controller\ExposeControllerInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;

/**
 * Action to create a new Being
 *
 */
class DebtExportController extends \TYPO3\Flow\Mvc\Controller\ActionController implements ExposeControllerInterface {

	/**
     * @Flow\Inject
     * @var \TYPO3\Flow\Resource\ResourceManager
     */
    protected $resourceManager;

    /**
     * @var \Famelo\Broensfin\Domain\Repository\ClaimRepository
     * @Flow\Inject
     */
    protected $claimRepository;

    /**
	 * @var \Famelo\Saas\Domain\Service\TransactionService
	 * @Flow\Inject
	 */
	protected $transactionService;

	/**
	 * @var \Famelo\Saas\Domain\Service\TransactionService
	 * @Flow\Inject(setting="paymentUrl")
	 */
	protected $paymentUrl;

	/**
	 * Create a new object
	 *
	 * @param string $type
	 * @return void
	 */
	public function indexAction($type) {
		$query = $this->claimRepository->createDebtorQuery();

		$userAgent = \parse_user_agent();

		$csv = new Writer(new \SplTempFileObject);
		$csv->setDelimiter(';');
		$csv->setNullHandlingMode(Writer::NULL_AS_EMPTY);
		$csv->insertOne(array(
			'creditor' => 'Creditor',
			'street' => 'Street',
			'city' => 'City',
			'zip' => 'Zip',
			'reference' => 'Reference',
			'currency' => 'Currency',
			'amount' => 'Amount',
			'status' => 'Status'
		));
		foreach ($query->execute() as $claim) {
			$csv->insertOne(array(
				$claim->getCreditor()->getName(),
				$claim->getCreditor()->getStreet(),
				$claim->getCreditor()->getCity(),
				$claim->getCreditor()->getZip(),
				$claim->getExternalReference(),
				$claim->getCurrency(),
				$claim->getAmount(),
				$claim->getCurrentState()
			));
		}
		$csv->output('Verbindlichkeiten - ' . date('d.m.Y') . ' -  UTF-8.csv');
		die;
	}

}

?>