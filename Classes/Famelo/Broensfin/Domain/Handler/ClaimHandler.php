<?php
namespace Famelo\Broensfin\Domain\Handler;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use Famelo\Common\Annotations as Common;

/**
 */
class ClaimHandler {
	/**
	 * @param \Famelo\Broensfin\Domain\Model\Claim $claim
	 * @return void
	 */
	public function created($claim) {
		if ($claim->getDebtor()->getNotify() === FALSE) {
			return;
		}
		$mail = new \Famelo\Messaging\Message();
		$mail->setMessage('Famelo.Broensfin:Claim/Created')
			 ->assign('claim', $claim)
			 ->send();
	}
	/**
	 * @param \Famelo\Broensfin\Domain\Model\Claim $claim
	 * @return void
	 */
	public function stateUpdated($claim) {
		$mail = new \Famelo\Messaging\Message();
		$mail->setMessage('Famelo.Broensfin:Claim/StateUpdated')
			 ->assign('claim', $claim)
			 ->send();
	}

	/**
	 * @param \Famelo\Broensfin\Domain\Model\ClaimComment $claimComment
	 * @return void
	 */
	public function commentAdded($claimComment) {
		if ($claimComment->getUser()->getTeam() !== $claimComment->getClaim()->getCreditor()) {
			$recipient = $claimComment->getClaim()->getCreditor();
		} else {
			$recipient = $claimComment->getClaim()->getDebtor();
		}
		$name = $claimComment->getUser()->getTeam()->__toString();
		$email = $recipient->getMainUser()->getEmail();
		$mail = new \Famelo\Messaging\Message();
		$mail->setMessage('Famelo.Broensfin:Claim/Comment')
			 ->setTo(array($email => $name))
			 ->assign('claimComment', $claimComment)
			 ->assign('name', $name)
			 ->send();
	}
}

?>