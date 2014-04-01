<?php
namespace Famelo\Broensfin\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use Famelo\Common\Annotations as Common;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Flow\Entity
 * @Common\Accessable
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Claim {
	/**
	 * @var \Famelo\Saas\Domain\Model\Team
	 * @ORM\ManyToOne
	 */
	protected $creditor;

	/**
	 * @var \Famelo\Saas\Domain\Model\Team
	 * @ORM\ManyToOne
	 */
	protected $debtor;

	/**
	 * @var string
	 */
	protected $reference = '';

	/**
	 * @var string
	 */
	protected $externalReference = '';

	/**
	 * @var string
	 */
	protected $currency;

	/**
	 * @var float
	 */
	protected $amount;

	/**
	 * @var \DateTime
	 */
	protected $dueDate;

	/**
	 * @var \DateTime
	 */
	protected $creationDate;

	/**
	 * @var \Famelo\Broensfin\Domain\Model\ClaimState
	 * @ORM\ManyToOne
	 */
	protected $currentState;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\Famelo\Broensfin\Domain\Model\ClaimState>
	 * @ORM\OneToMany(mappedBy="claim", cascade="all")
	 * @ORM\OrderBy({"date" = "DESC"})
	 */
	protected $states;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\Famelo\Broensfin\Domain\Model\ClaimComment>
	 * @ORM\OneToMany(mappedBy="claim")
	 * @ORM\OrderBy({"date" = "DESC"})
	 */
	protected $comments;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $deletedAt;

	public function __construct(\TYPO3\Flow\Security\Context $securityContext = NULL) {
		$this->currentState = new ClaimState(ClaimState::STATE_PENDING);
		$this->currentState->setClaim($this);
		if ($securityContext !== NULL) {
			$this->currency = $securityContext->getParty()->getTeam()->getCurrency();
		}
		$this->creationDate = new \DateTime();
	}

	public function __toString() {
		return $this->externalReference;
	}

	/**
     * @ORM\PostPersist
     */
    public function postPersist() {
        $this->emitCreated($this);
    }

	/**
	 * @param \Famelo\Broensfin\Domain\Model\Claim $claim
	 * @return void
	 */
	public function updateState($state) {
		$state = new ClaimState($state);
		$state->setClaim($this);
		$this->getStates()->add($state);
		$this->currentState = $state;

		$this->emitStateUpdated($this);
	}

	/**
	 * @param \Famelo\Broensfin\Domain\Model\Claim $claim
	 * @return void
	 * @Flow\Signal
	 */
	protected function emitStateUpdated($claim) {
		return;
	}

	/**
	 * @param \Famelo\Broensfin\Domain\Model\Claim $claim
	 * @return void
	 * @Flow\Signal
	 */
	protected function emitCreated($claim) {
		return;
	}
}

?>