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
class ClaimState {
	const STATE_PENDING = 'pending';
	const STATE_ACCEPTED = 'accepted';
	const STATE_REJECTED = 'rejected';
	const STATE_BPOACCEPTED = 'bpo';

	/**
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * @var \Famelo\Broensfin\Domain\Model\Claim
	 * @ORM\ManyToOne(inversedBy="states")
	 */
	protected $claim;

	/**
	 * @var string
	 */
	protected $state;

	/**
	 * Constructs this comment
	 *
	 */
	public function __construct($state) {
		$this->date = new \DateTime();
		$this->state = $state;
	}

	public function __toString() {
		return $this->getState();
	}

	public function isAccepted() {
		return $this->getState() == self::STATE_ACCEPTED;
	}

	public function getRejected() {
		return $this->getState() == self::STATE_REJECTED;
	}

	public function getPending() {
		return $this->getState() == self::STATE_PENDING;
	}
}

?>