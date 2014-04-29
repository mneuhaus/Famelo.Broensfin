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
use Famelo\Broensfin\Domain\Model\ClaimComment;
use Famelo\Broensfin\Domain\Model\ClaimState;
use TYPO3\Expose\Controller\ExposeControllerInterface;
use TYPO3\Flow\Annotations as Flow;

/**
 * Action to create a new Being
 *
 */
class DetailController extends \TYPO3\Flow\Mvc\Controller\ActionController implements ExposeControllerInterface {
	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	/**
	 * @var \Famelo\Broensfin\Domain\Repository\ClaimRepository
	 * @Flow\Inject
	 */
	protected $claimRepository;

	/**
	 * @return void
	 */
	public function initializeIndexAction() {
		$this->arguments['objects']->setDataType('Doctrine\Common\Collections\Collection<' . $this->request->getArgument('type') . '>');
		$this->arguments['objects']->getPropertyMappingConfiguration()->allowAllProperties();
	}

	/**
	 * Create a new object
	 *
	 * @param string $type
	 * @param \Doctrine\Common\Collections\Collection $objects
	 * @return void
	 */
	public function indexAction($type, $objects = NULL) {
		$this->view->assign('className', $type);
		$this->view->assign('claim', $objects->current());
		$this->view->assign('user', $this->securityContext->getParty());
	}

	/**
	 * @param \Famelo\Broensfin\Domain\Model\Claim $claim
	 * @return void
	 */
	public function acceptAction($claim) {
		$claim->updateState(ClaimState::STATE_ACCEPTED);
		$this->claimRepository->update($claim);
		$this->redirect('index', NULL, NULL, array(
			'objects' => array($claim)
		));
	}

	/**
	 * @param \Famelo\Broensfin\Domain\Model\Claim $claim
	 * @return void
	 */
	public function rejectAction($claim) {
		$claim->updateState(ClaimState::STATE_REJECTED);
		$this->claimRepository->update($claim);
		$this->redirect('index', NULL, NULL, array(
			'objects' => array($claim)
		));
	}

	/**
	 * @param \Famelo\Broensfin\Domain\Model\Claim $claim
	 * @return void
	 */
	public function bpoAction($claim) {
		$claim->updateState(ClaimState::STATE_BPOACCEPTED);
		$this->claimRepository->update($claim);
		$this->redirect('index', NULL, NULL, array(
			'objects' => array($claim)
		));
	}

	/**
	 * @param \Famelo\Broensfin\Domain\Model\Claim $claim
	 * @param string $comment
	 * @return void
	 */
	public function commentAction($claim, $comment) {
		$comment = new ClaimComment($comment);
		$comment->setClaim($claim);
		$claim->addComment($comment);
		$this->claimRepository->update($claim);
		$this->emitClaimCommentAdded($comment);
		$this->redirect('index', NULL, NULL, array(
			'objects' => array($claim)
		));
	}

	/**
	 * @param \Famelo\Broensfin\Domain\Model\ClaimComment $claimComment
	 * @return void
	 * @Flow\Signal
	 */
	protected function emitClaimCommentAdded($claimComment) {
		return;
	}

	// /**
	//  * @param string $type
	//  */
	// public function createAction($type) {
	// 	$objects = $this->request->getInternalArgument('__objects');
	// 	foreach ($objects as $object) {
	// 		$this->persistenceManager->add($object);
	// 	}
	// 	$this->persistenceManager->persistAll();
	// 	$this->redirect('index', 'list', 'TYPO3.Expose', array('type' => $type));
	// }
}

?>