<?php
namespace Famelo\Broensfin\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Broensfin".      *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class ContactController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var string
	 * @Flow\Inject(setting="recipients.admin", package="Famelo.Messaging")
	 */
	protected $receipients;

	/**
	 * @return void
	 */
	public function indexAction() {
	}

	/**
	 * @param string $name
	 * @param string $email
	 * @param string $message
	 * @return void
	 */
	public function submitAction($name = NULL, $email = NULL, $message = NULL) {
		$mail = new \Famelo\Messaging\Message();
		$mail->setMessage('Famelo.Broensfin:Contact')
			 ->setRecipientGroup('admin')
			 ->assign('name', $name)
			 ->assign('email', $email)
			 ->assign('message', $message)
			 ->send();
	}

}