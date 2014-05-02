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
use TYPO3\Expose\Controller\ExposeControllerInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;

/**
 * Action to create a new Being
 *
 */
class DebtImportController extends \TYPO3\Flow\Mvc\Controller\ActionController implements ExposeControllerInterface {

	/**
     * @Flow\Inject
     * @var \TYPO3\Flow\Resource\ResourceManager
     */
    protected $resourceManager;

    /**
     * @var \Famelo\Saas\Domain\Repository\TeamRepository
     * @Flow\Inject
     */
    protected $teamRepository;

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
	 * @param \Doctrine\Common\Collections\Collection $objects
	 * @return void
	 */
	public function indexAction($type, $objects = NULL) {
		if ($objects === NULL) {
			$objects = array(new $type());
		}
		$this->view->assign('className', $type);
		$this->view->assign('objects', $objects);
		$this->view->assign('callbackAction', 'create');
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
		$reader->setDelimiter(';');
		$columns = array(
			'creditor' => 'Creditor',
			'street' => 'Street',
			'city' => 'City',
			'zip' => 'Zip',
			'reference' => 'Reference',
			'currency' => 'Currency',
			'amount' => 'Amount',
			'state' => 'Status'
		);
		if ($reader->validate($columns) === FALSE) {
			$this->view->assign('error', 'invalid csv');
			return;
		}

		$knownStates = $this->getKnownStates();

		$counter = 0;
		$rows = array();
		foreach ($reader->fetchAllAssoc() as $input) {
			$claims = $this->claimRepository->findByCsv($input);
			$row = array();
			foreach ($input as $key => $value) {
				$row[array_search($key, $columns)] = $value;
			}
			$row['claims'] = $claims;
			if (in_array($row['state'], $knownStates) === FALSE) {
				// $row['status'] = 'multiple';
				// $counter++;
				$row['status'] = 'unkownState';
			} else if ($claims->count() > 1) {
				// $row['status'] = 'multiple';
				// $counter++;
				$row['status'] = 'none';
			} else if ($claims->count() > 0) {
				$row['status'] = 'one';
				$row['claim'] = $claims->getFirst();
				$counter++;
			} else {
				$row['status'] = 'none';
			}
			$rows[] = $row;
		}

		$this->view->assign('columns', $columns);
		$this->view->assign('rows', $rows);
		$this->view->assign('file', $file->getUri());
	}

    /**
     * Imports an image
     *
     * @param string $file
     * @return void
     */
	public function importAction($file) {
		$reader = new Reader($file);
		$reader->setDelimiter(';');
		$columns = array(
			'creditor' => 'Creditor',
			'street' => 'Street',
			'city' => 'City',
			'zip' => 'Zip',
			'reference' => 'Reference',
			'currency' => 'Currency',
			'amount' => 'Amount',
			'state' => 'Status'
		);
		if ($reader->validate($columns) === FALSE) {
			$this->view->assign('error', 'invalid csv');
			return;
		}
		$knownStates = $this->getKnownStates();

		$claims = $this->request->getArgument('claims');
		foreach ($reader->fetchAllAssoc() as $key => $row) {
			if (!isset($claims[$key])) {
				continue;
			}
			if (in_array($row['Status'], $knownStates) === FALSE) {
				continue;
			}

			$claimIdentifier = $claims[$key];
			$claim = $this->claimRepository->findByIdentifier($claimIdentifier);
			$claim->updateState($row['Status']);
			$this->persistenceManager->update($claim);
		}

		$this->flashMessageContainer->addMessage(new Message('Import successfull'));
		$this->redirect('index', 'list', 'TYPO3.Expose', array('type' => '\Famelo\Broensfin\Domain\Model\Claim'));
	}

	public function getKnownStates() {
		$reflection = new \ReflectionClass('\Famelo\Broensfin\Domain\Model\ClaimState');
		return $reflection->getConstants();
	}
}

?>