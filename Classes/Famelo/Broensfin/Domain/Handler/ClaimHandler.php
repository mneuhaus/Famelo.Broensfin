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
	 * @Flow\Slot(class="Famelo\Broensfin\Domain\Model\Claim", signal="created")
	 */
	public function created($claim) {
		$mail = new \Famelo\Messaging\Message();
		$mail->setMessage('Famelo.Broensfin:Claim/Created')
			 ->assign('claim', $claim)
			 ->send();
	}
	/**
	 * @param \Famelo\Broensfin\Domain\Model\Claim $claim
	 * @return void
	 * @Flow\Slot(class="Famelo\Broensfin\Domain\Model\Claim", signal="stateUpdated")
	 */
	public function stateUpdated($claim) {
		$mail = new \Famelo\Messaging\Message();
		$mail->setMessage('Famelo.Broensfin:Claim/Accepted')
			 ->assign('claim', $claim)
			 ->send();
	}

	/**
	 * @param \Famelo\Broensfin\Domain\Model\ClaimComment $claimComment
	 * @return void
	 * @Flow\Slot(class="Famelo\Broensfin\Controller\Claim\DetailController", signal="claimCommentAdded")
	 */
	public function commentAdded($claimComment) {
		$mail = new \Famelo\Messaging\Message();
		$mail->setMessage('Famelo.Broensfin:Claim/Comment')
			 ->assign('claimComment', $claimComment)
			 ->send();
	}
}

?>