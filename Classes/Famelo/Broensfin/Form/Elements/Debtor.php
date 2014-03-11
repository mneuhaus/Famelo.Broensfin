<?php
namespace Famelo\Broensfin\Form\Elements;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Famelo\Saas\Domain\Model\Team;
use Famelo\Saas\Domain\Model\User;
use TYPO3\Expose\Form\Elements\OptionsFormElement;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Party\Domain\Model\PersonName;

/**
 * Custom form Element
 */
class Debtor extends OptionsFormElement {

	/**
	 * @var \TYPO3\Flow\Persistence\Doctrine\PersistenceManager
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	public function onSubmit(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime, &$elementValue) {
		if (strlen($elementValue['existing']) > 0 && empty($elementValue['email'])) {
			$elementValue = $elementValue['existing'];
		} else {
			$user = new User();
			$name = new PersonName();
			$name->setFirstName($elementValue['firstname']);
			$name->setLastName($elementValue['lastname']);
			$user->setName($name);
			$user->setEmail($elementValue['email']);
			$user->setAccounts(new \Doctrine\Common\Collections\ArrayCollection());
			$this->persistenceManager->add($user);
			$team = new Team();
			$team->setName($elementValue['company']);
			$team->setUsers(array($user));
			$this->persistenceManager->add($team);
			$this->persistenceManager->persistAll();
			$identifier = $this->persistenceManager->getIdentifierByObject($team);
			$mail = new \Famelo\Messaging\Message();
			$mail->setMessage('Famelo.Saas:User/Invite')
					->assign('user', $user)
					->send();
			$elementValue = $identifier;
		}
		// do something with the elementValue
	}

}