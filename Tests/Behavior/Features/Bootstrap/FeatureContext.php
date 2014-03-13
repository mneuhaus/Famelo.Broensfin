<?php
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Driver\Selenium2Driver;
use Famelo\Messaging\Transport\DebugTransport;
use Famelo\Saas\Domain\Model\Subscription;
use Famelo\Saas\Domain\Model\Transaction;
use PHPUnit_Framework_Assert as Assert;
use TYPO3\Flow\Utility\Arrays;

require_once __DIR__ . '/../../../../../../Application/Flowpack.Behat/Tests/Behat/FlowContext.php';
/**
 * Features context
 */
class FeatureContext extends MinkContext {

    /**
     * @var \TYPO3\Flow\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Famelo\Saas\Domain\Model\Team
     */
    protected $currentTeam;

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
     * TODO: Document this Method! ( getTeam )
     */
    public function getTeam($name = NULL) {
        $teamRepository = $this->objectManager->get('Famelo\\Saas\\Domain\\Repository\\TeamRepository');
        if ($name !== NULL) {
            $teams = $teamRepository->findByName($name);
            if ($teams->count() < 1) {
                Assert::fail('Team "' . $name . '" not found');
            }
            return $teams->getFirst();
        }

        return $teamRepository->findByIdentifier($this->currentTeam);
    }

    /**
     * @BeforeStep
     */
    public function beforeStep() {
        try {
            preg_match('/<meta http-equiv="refresh" content="([0-9]*);url=([^"]*)/', $this->getSession()->getPage()->getHtml(), $match);
            if (isset($match[2])) {
                $this->visit($match[2]);
            }
        } catch(\Exception $e) {}
    }

    /**
     * @BeforeScenario @email
     */
    public function resetEmails($event) {
        DebugTransport::clearEmails();
    }

    /**
    * TODO: Document this Method! ( createTeams )
    */
    public function createTeams($rows) {
        $teamFactory = $this->objectManager->get('Famelo\\Saas\\Domain\\Factory\\TeamFactory');
        $teamRepository = $this->objectManager->get('Famelo\\Saas\\Domain\\Repository\\TeamRepository');
        $accountRepository = $this->objectManager->get('TYPO3\\Flow\\Security\\AccountRepository');
        $teams = array();
        foreach ($rows as $row) {
            $team = $teamFactory->create($row['team'], $row['username'], $row['password'], $row['email'], $row['firstname'], $row['lastname'], array($row['role']
            ));
            $teamFactory->preSave($team);
            $teamRepository->add($team);
            $teams[] = $team;
        }
        $this->getSubcontext('flow')->persistAll();
        return $teams;
    }

    /**
     * @Given /^I am logged in as a customer$/
     */
    public function iAmLoggedInAsACustomer() {
        $teams = $this->createTeams(array(array('team' => 'Toni',
                'username' => 'toni',
                'password' => 'tester',
                'email' => 'toni@foo.com',
                'firstname' => 'Toni',
                'lastname' => 'Toni',
                'role' => 'Famelo.Saas:Customer'
            )
        ));
        $team = current($teams);
        $this->currentTeam = $team->getIdentifier();
        $this->visit('/login.html');
        $this->fillField('Username', 'toni');
        $this->fillField('Password', 'tester');
        $this->pressButton('Login');
    }

    /**
     * @Given /^I am not authenticated$/
     */
    public function iAmNotAuthenticated() {

    }

    /**
     * @When /^i execute an action that costs "([^"]*)" "([^"]*)"$/
     */
    public function iExecuteAnActionThatCosts($amount, $currency) {
        $transactionService = $this->objectManager->get('Famelo\Saas\Domain\Service\TransactionService');
        $transactionService->setUser($this->getTeam()->getMainUser());
        $transaction = new Transaction();
        $transaction->setAmount(-$amount);
        $transaction->setCurrency($currency);
        $transactionService->addTransaction($transaction);
    }

    /**
     * @Given /^I have a balance of "([^"]*)"$/
     */
    public function iHaveABalanceOf($balance) {
        if ($this->getSession()->getCurrentUrl() !== '/mein-konto.html') {
            $this->visit('/mein-konto.html');
        }
        Assert::assertEquals($balance, $this->getSession()->getPage()->find('css', '.balance')->getAttribute('data-balance'));
    }

    /**
     * @Given /^I have received an email with the subject "([^"]*)"$/
     */
    public function iHaveReceivedAnEmailWithTheSubject($subject) {
        $emails = DebugTransport::getEmails($this->getTeam()->getMainUser()->getEmail());
        foreach ($emails as $email) {
            if ($email['subject'] === $subject) {
                return;
            }
        }
        Assert::fail('Email with the subject "' . $subject . '" not found');
    }

    /**
     * @Given /^I imported the site "([^"]*)"$/
     */
    public function iImportedTheSite($packageKey) {
        $nodeDataRepository = $this->objectManager->get('TYPO3\\TYPO3CR\\Domain\\Repository\\NodeDataRepository');
        $contextFactory = $this->objectManager->get('TYPO3\\TYPO3CR\\Domain\\Service\\ContextFactoryInterface');
        $contentContext = $contextFactory->create(array('workspace' => 'live'
        ));
        \TYPO3\Flow\Reflection\ObjectAccess::setProperty($nodeDataRepository, 'context', $contentContext, TRUE);
        $siteImportService = $this->objectManager->get('TYPO3\\Neos\\Domain\\Service\\SiteImportService');
        $siteImportService->importFromPackage($packageKey, $contentContext);
        $this->getSubcontext('flow')->persistAll();
        $policyService = $this->objectManager->get('TYPO3\\Flow\\Security\\Policy\\PolicyService');
        $policyService->initializeRolesFromPolicy();
    }

    /**
     * @Then /^I should be logged in$/
     */
    public function iShouldBeLoggedIn() {
        $this->assertSession()->elementExists('css', '.btn-logout');
    }

    /**
     * @Then /^I should be on page "([^"]*)"$/
     */
    public function iShouldBeOnPage($page) {
        $this->assertSession()->addressEquals($page);
    }

    /**
     * @Then /^I should be see a flash message "([^"]*)"$/
     */
    public function iShouldBeSeeAFlashMessage($message) {
        $this->assertSession()->elementTextContains('css', '.alert', $message);
    }

    /**
     * @Then /^I should find a new Team "([^"]*)"$/
     */
    public function iShouldFindANewTeam($name) {
        $team = $this->getTeam($name);
        Assert::assertEquals($name, $team->getName());
    }

    /**
     * @Then /^I should see a login form$/
     */
    public function iShouldSeeALoginForm() {
        $this->assertSession()->fieldExists('Username');
        $this->assertSession()->fieldExists('Password');
    }

    /**
     * @Given /^I should see my Transactions$/
     */
    public function iShouldSeeMyTransactions() {
        $this->assertSession()->elementExists('css', '.transactions');
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

    /**
     * @When /^(?:|I )wait (\d+) secs$/
     */
    public function iWaitSecs($seconds) {
        sleep($seconds);
    }

    /**
     * Take screenshot when step fails.
     * Works only with Selenium2Driver.
     *
     * @AfterStep
     */
    public function takeScreenshotAfterFailedStep($event) {
        if ($event->getResult() === 4) {
            try {
                $driver = $this->getSession()->getDriver();
                if ($driver instanceof Selenium2Driver) {
                    $this->iTakeAScreenshot();
                }
                if ($driver instanceof GoutteDriver) {
                    $filename = sprintf('%s_%s_%s.%s', 'Goutte', date('c'), uniqid('', true), 'html');
                    $filepath = FLOW_PATH_ROOT . '/Screenshots';
                    if (!is_dir($filepath)) {
                        mkdir($filepath);
                    }
                    file_put_contents($filepath . '/' . $filename, $this->getSession()->getPage()->getHtml());
                }
            } catch(\Exception $e) {}
        }
    }

    /**
     * @Given /^the following teams exist:$/
     */
    public function theFollowingTeamsExist(TableNode $table) {
        $rows = $table->getHash();
        $this->createTeams($rows);
    }

    /**
     * @Given /^the last transaction amount is "([^"]*)"$/
     */
    public function theLastTransactionAmountIs($amount) {
        Assert::assertEquals($amount, $this->getSession()->getPage()->find('css', '.transactions .first .transaction-amount')->getAttribute('data-amount'));
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

}
?>