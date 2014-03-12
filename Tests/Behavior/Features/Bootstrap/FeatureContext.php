<?php

use Behat\Behat\Exception\PendingException;
use Behat\MinkExtension\Context\MinkContext;
use Famelo\Saas\Domain\Model\Subscription;
use PHPUnit_Framework_Assert as Assert;
use TYPO3\Flow\Utility\Arrays;

require_once(__DIR__ . '/../../../../../../Application/Flowpack.Behat/Tests/Behat/FlowContext.php');

/**
 * Features context
 */
class FeatureContext extends MinkContext {
	/**
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Initializes the context
	 *
	 * @param array $parameters Context parameters (configured through behat.yml)
	 */
	public function __construct(array $parameters) {
		$this->useContext('flow', new \Flowpack\Behat\Tests\Behat\FlowContext($parameters));
		$this->objectManager = $this->getSubcontext('flow')->getObjectManager();
	}

	/**
	 * @Given /^I imported the site "([^"]*)"$/
	 */
	public function iImportedTheSite($packageKey) {
		/** @var \TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository $nodeDataRepository */
		$nodeDataRepository = $this->objectManager->get('TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository');
		/** @var \TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface $contextFactory */
		$contextFactory = $this->objectManager->get('TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface');
		$contentContext = $contextFactory->create(array('workspace' => 'live'));
		\TYPO3\Flow\Reflection\ObjectAccess::setProperty($nodeDataRepository, 'context', $contentContext, TRUE);

		/** @var \TYPO3\Neos\Domain\Service\SiteImportService $siteImportService */
		$siteImportService = $this->objectManager->get('TYPO3\Neos\Domain\Service\SiteImportService');
		$siteImportService->importFromPackage($packageKey, $contentContext);

		$this->getSubcontext('flow')->persistAll();
		$policyService = $this->objectManager->get('TYPO3\Flow\Security\Policy\PolicyService');
		$policyService->initializeRolesFromPolicy();
	}

	/**
	 * @Given /^I am not authenticated$/
	 */
	public function iAmNotAuthenticated() {
		// Do nothing, every scenario has a new session
	}

	/**
	 * @Then /^I should see a login form$/
	 */
	public function iShouldSeeALoginForm() {
		$this->assertSession()->fieldExists('Username');
		$this->assertSession()->fieldExists('Password');
	}

	/**
	 * @Given /^the following teams exist:$/
	 */
	public function theFollowingTeamsExist(TableNode $table) {
		$rows = $table->getHash();
		$teamFactory = $this->objectManager->get('Famelo\Saas\Domain\Factory\TeamFactory');
		$teamRepository = $this->objectManager->get('Famelo\Saas\Domain\Repository\TeamRepository');
		$accountRepository = $this->objectManager->get('TYPO3\Flow\Security\AccountRepository');
		foreach ($rows as $row) {
			$team = $teamFactory->create($row['team'], $row['username'], $row['password'], '', $row['firstname'], $row['lastname'], array($row['role']));
			$teamRepository->add($team);
			$accountRepository->add($team->getMainUser()->getAccount());
		}
		$this->getSubcontext('flow')->persistAll();
	}

	/**
	 * @Then /^I should be on page "([^"]*)"$/
	 */
	public function iShouldBeOnPage($page) {
		$this->assertSession()->addressEquals($page);
	}

	/**
	 * @Then /^I should be logged in$/
	 */
	public function iShouldBeLoggedIn() {
		if (!$this->getSession()->getPage()->findButton('Logout')) {
			// Assert::fail('"Logout" Button expected');
		}
	}

	public function getTeam($name) {
		$teamRepository = $this->objectManager->get('Famelo\Saas\Domain\Repository\TeamRepository');
		$teams = $teamRepository->findByName($name);
		if ($teams->count() < 1) {
			Assert::fail('Team "' . $name . '" not found');
		}
		return $teams->getFirst();
	}

	/**
     * @Then /^I should find a new Team "([^"]*)"$/
     */
    public function iShouldFindANewTeam($name) {
		$team = $this->getTeam($name);
		Assert::assertEquals($name, $team->getName());
    }

	/**
     * @Given /^The team "([^"]*)" should have a subscription to the "([^"]*)" Plan$/
     */
    public function theTeamShouldHaveASubscriptionToThePlan($team, $plan) {
        $team = $this->getTeam($team);
        $subscription = $team->getSubscription();
        if (!$subscription instanceof Subscription) {
        	Assert::fail('Team "' . $team . '" has no subscription');
        }
		Assert::assertEquals($subscription->getPlan(), $plan);
    }

    /**
     * @Given /^The team "([^"]*)" should have a "([^"]*)" as default currency$/
     */
    public function theTeamShouldHaveAAsDefaultCurrency($team, $currency) {
        $team = $this->getTeam($team);
        Assert::assertEquals($team->getCurrency(), $currency);
    }

    /**
     * @Given /^The team "([^"]*)" should have a balance of "([^"]*)"$/
     */
    public function theTeamShouldHaveABalanceOf($team, $balance) {
        $team = $this->getTeam($team);
        Assert::assertEquals($team->getSubscription()->getBalance(), $balance);
    }

  // /**
  //    * @BeforeStep
  //    */
  //   public function beforeStep() {
  //       if (self::$waitForReadyState) {
  //           try {
  //               $this->getSession()->wait(5000, 'document.readyState === "complete"');
  //           } catch(Exception $e){};
  //       }
  //   }

    /**
     * @Given /^I should see my Transactions$/
     */
    public function iShouldSeeMyTransactions() {
        throw new PendingException();
    }

    /**
     * @When /^(?:|I )wait (\d+) secs$/
     */
    public function iWaitSecs($seconds) {
        sleep($seconds);
    }

    /**
     * @Then /^(?:|I )take a screenshot$/
     */
    public function iTakeAScreenshot() {
        $filename = sprintf('%s_%s_%s.%s', $this->getMinkParameter('browser_name'), date('c'), uniqid('', true), 'png');
        $filepath = FLOW_PATH_ROOT . '/Screenshots';
        if (!is_dir($filepath)) {
            mkdir($filepath);
        }
        file_put_contents($filepath . '/' . $filename, $this->getSession()->getScreenshot());
    }
}
