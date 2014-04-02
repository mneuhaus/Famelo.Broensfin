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
class ExportController extends \TYPO3\Flow\Mvc\Controller\ActionController implements ExposeControllerInterface {

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
		$query = $this->claimRepository->createCreditorQuery();

		$userAgent = \parse_user_agent();

		$csv = new Writer(new \SplTempFileObject);
		$csv->setDelimiter(';');
		$csv->setNullHandlingMode(Writer::NULL_AS_EMPTY);
		$csv->insertOne(array(
			'debtor' => 'Debtor',
			'street' => 'Street',
			'city' => 'City',
			'zip' => 'Zip',
			'reference' => 'Reference',
			'currency' => 'Currency',
			'amount' => 'Amount',
			'dueDate' => 'Due Date',
			'creationDate' => 'Creation Date'
		));
		foreach ($query->execute() as $claim) {
			$csv->insertOne(array(
				$claim->getDebtor()->getName(),
				$claim->getDebtor()->getStreet(),
				$claim->getDebtor()->getCity(),
				$claim->getDebtor()->getZip(),
				$claim->getExternalReference(),
				$claim->getCurrency(),
				$claim->getAmount(),
				$claim->getDueDate()->format('d.m.Y'),
				$claim->getCreationDate()->format('d.m.Y')
			));
		}
		$csv->output('Forderungen - ' . date('d.m.Y') . ' -  UTF-8.csv');
		die;
	}

    /**
     * Imports an image
     *
     * @param \TYPO3\Flow\Resource\Resource $file
     * @return void
     */
	public function checkAction($file) {
		$this->persistenceManager->add($file);
		$reader = new Reader($file->getUri());
		$columns = array(
			'debtor' => 'Debtor',
			'street' => 'Street',
			'city' => 'City',
			'zip' => 'Zip',
			'reference' => 'Reference',
			'currency' => 'Currency',
			'amount' => 'Amount',
			'dueDate' => 'Due Date',
			'creationDate' => 'Creation Date'
		);
		if ($reader->validate($columns) === FALSE) {
			$this->view->assign('error', 'invalid csv');
			return;
		}

		$counter = 0;
		$rows = array();
		foreach ($reader->fetchAllAssoc() as $input) {
			$teams = $this->teamRepository->findByCsv($input);
			$row = array();
			foreach ($input as $key => $value) {
				$row[array_search($key, $columns)] = $value;
			}
			$row['teams'] = $teams;
			if ($teams->count() > 1) {
				$row['status'] = 'multiple';
				$counter++;
			} else if ($teams->count() > 0) {
				$row['status'] = 'one';
				$row['team'] = $teams->getFirst();
				$counter++;
			} else {
				$row['status'] = 'none';
			}
			$rows[] = $row;
		}

		$transaction = new Transaction();
		$transaction->setAmount(-$counter);
		$transaction->setCurrency('POINT');
		if ($this->transactionService->hasFunds($transaction) === FALSE) {
			$this->flashMessageContainer->addMessage(new Message('Insufficient funds'));
			$this->redirectToUri($this->paymentUrl);
		}

		$this->view->assign('columns', $columns);
		$this->view->assign('rows', $rows);
		$this->view->assign('file', $file->getUri());
	}

    /**
     * Imports an image
     *
     * @param string $file
     * @param array $teams
     * @return void
     */
	public function importAction($file, $teams) {
		$reader = new Reader($file);
		$columns = array(
			'debtor' => 'Debtor',
			'street' => 'Street',
			'city' => 'City',
			'zip' => 'Zip',
			'reference' => 'Reference',
			'currency' => 'Currency',
			'amount' => 'Amount',
			'dueDate' => 'Due Date',
			'creationDate' => 'Creation Date'
		);
		if ($reader->validate($columns) === FALSE) {
			$this->view->assign('error', 'invalid csv');
			return;
		}

		$counter = 0;
		foreach ($reader->fetchAllAssoc() as $key => $row) {
			if (!isset($teams[$key])) {
				continue;
			}
			$counter++;

			$teamIdentifier = $teams[$key];
			$debtor = $this->teamRepository->findByIdentifier($teamIdentifier);
			$claim = new Claim();
			$claim->setCreditor($this->transactionService->getTeam());
			$claim->setDebtor($debtor);
			$claim->setExternalReference($row['Reference']);
			$claim->setCurrency($row['Currency']);
			$claim->setAmount($row['Amount']);
			$claim->setDueDate(new \DateTime($row['Due Date']));
			$claim->setCreationDate(new \DateTime($row['Creation Date']));
			$this->persistenceManager->add($claim);
		}

		if ($counter > 0) {
			$transaction = new Transaction();
			$transaction->setAmount(-$counter);
			$transaction->setCurrency('POINT');
			if ($this->transactionService->hasFunds($transaction) === FALSE) {
				$this->flashMessageContainer->addMessage(new Message('Insufficient funds'));
				$this->redirectToUri($this->paymentUrl);
			}
			$transaction->setNote('Imported multiple Claims');
			$this->transactionService->addTransaction($transaction);
		}

		$this->flashMessageContainer->addMessage(new Message('Import successfull'));
		$this->redirect('index', 'list', 'TYPO3.Expose', array('type' => '\Famelo\Broensfin\Domain\Model\Claim'));
	}

	/**
	 * @param string $type
	 */
	public function createAction($type) {
		$objects = $this->request->getInternalArgument('__objects');
		foreach ($objects as $object) {
			$this->persistenceManager->add($object);
		}
		$this->persistenceManager->persistAll();
		$this->redirect('index', 'list', 'TYPO3.Expose', array('type' => $type));
	}

}

?>