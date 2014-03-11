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
use EasyCSV\Reader;
use TYPO3\Expose\Controller\ExposeControllerInterface;
use TYPO3\Flow\Annotations as Flow;

/**
 * Action to create a new Being
 *
 */
class ImportController extends \TYPO3\Flow\Mvc\Controller\ActionController implements ExposeControllerInterface {

	/**
     * @Flow\Inject
     * @var \TYPO3\Flow\Resource\ResourceManager
     */
    protected $resourceManager;

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
		$reader = new Reader($file->getUri());
		$rows = $reader->getAll();
		if (array_keys(current($rows)) !== array('Creditor', 'Debtor', 'Reference', 'Currency', 'Amount', 'Due Date', 'Creation Date')) {

		}
		var_dump($reader->getAll());
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