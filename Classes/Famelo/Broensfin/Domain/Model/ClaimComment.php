<?php
namespace Famelo\Broensfin\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use Famelo\Common\Annotations as Common;

/**
 * @Flow\Entity
 * @Common\Accessable
 * @ORM\HasLifecycleCallbacks
 */
class ClaimComment {
	/**
	 * @var \DateTime
	 * @Flow\Identity
	 */
	protected $date;

	/**
	 * @var \Famelo\Broensfin\Domain\Model\Claim
	 * @ORM\ManyToOne(inversedBy="comments")
	 */
	protected $claim;

	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $message;

	/**
	 * @var \Famelo\Saas\Domain\Model\User
	 * @ORM\ManyToOne
	 */
	protected $user;

	/**
	 * @param string $message
	 * @param \TYPO3\Flow\Security\Context $securityContext
	 */
	public function __construct($message, \TYPO3\Flow\Security\Context $securityContext) {
		$this->date = new \DateTime();
		$this->user = $securityContext->getParty();
		$this->message = $message;
	}
}

?>