<?php
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Driver\Selenium2Driver;
use Famelo\Broensfin\Domain\Model\Claim;
use Famelo\Messaging\Transport\DebugTransport;
use Famelo\Saas\Domain\Model\Subscription;
use Famelo\Saas\Domain\Model\Transaction;
use PHPUnit_Framework_Assert as Assert;
use Symfony\Component\DomCrawler\Crawler;
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

        return $this->currentTeam;
    }

    /**
     * @BeforeStep
     */
    public function beforeStep() {
        try {
            while (preg_match('/<meta http-equiv="refresh" content="([0-9]*);url=([^"]*)/', $this->getSession()->getPage()->getHtml(), $match) == 1) {
                if (isset($match[2])) {
                    $url = str_replace('&amp;', '&', $match[2]);
                    // echo 'Redirecting to url: ' . $url . chr(10);
                    $this->visit($url);
                }
            }

            if (stristr($this->getSession()->getPage()->getHtml(), 'Uncaught Exception in Flow')) {
                $this->iTakeAScreenshot();
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
     * @When /^the following claims exist:$/
     */
    public function theFollowingClaimsExist(TableNode $table) {
        $rows = $table->getHash();
        $teamRepository = $this->objectManager->get('Famelo\\Saas\\Domain\\Repository\\TeamRepository');
        $claimRepository = $this->objectManager->get('Famelo\\Broensfin\\Domain\\Repository\\ClaimRepository');
        $propertyMapper = $this->objectManager->get('TYPO3\\Flow\\Property\\PropertyMapper');
        $teams = array();
        foreach ($rows as $row) {
            $row['creditor'] = $this->getTeam($row['creditor'])->getIdentifier();
            $row['debtor'] = $this->getTeam($row['debtor'])->getIdentifier();
            $row['dueDate'] = array(
                'date' => $row['dueDate'],
                'dateFormat' => 'd.m.Y'
            );
            $row['creationDate'] = array(
                'date' => $row['creationDate'],
                'dateFormat' => 'd.m.Y'
            );
            $claim = $propertyMapper->convert($row, '\Famelo\Broensfin\Domain\Model\Claim');
            $claimRepository->add($claim);
        }
        $this->getSubcontext('flow')->persistAll();
    }

    /**
     * Looks for a table, then looks for a row that contains the given text.
     * Once it finds the right row, it clicks a link in that row.
     *
     * Really handy when you have a generic "Edit" link on each row of
     * a table, and you want to click a specific one (e.g. the "Edit" link
     * in the row that contains "Item #2")
     *
     * @When /^I click on "([^"]*)" on the row containing "([^"]*)"$/
     */
    public function iClickOnOnTheRowContaining($linkName, $rowText) {
        /** @var $row \Behat\Mink\Element\NodeElement */
        $row = $this->getSession()->getPage()->find('css', sprintf('table tr:contains("%s")', $rowText));
        if (!$row) {
            throw new \Exception(sprintf('Cannot find any row on the page containing the text "%s"', $rowText));
        }

        $row->clickLink($linkName);
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
            $team = $teamFactory->create(
                $row['team'],
                $row['username'],
                $row['password'],
                $row['email'],
                $row['firstname'],
                $row['lastname'],
                array($row['role'])
            );
            $teamFactory->preSave($team);
            $teamRepository->add($team);
            $accountRepository->add($team->getMainUser()->getAccount());
            $teams[] = $team;
        }
        $this->getSubcontext('flow')->persistAll();
        return $teams;
    }

    /**
     * @Given /^I am logged in as "([^"]*)" "([^"]*)"$/
     */
    public function iAmLoggedInAsACustomer($username, $password) {
        $accountRepository = $this->objectManager->get('TYPO3\\Flow\\Security\\AccountRepository');
        $account = $accountRepository->findOneByAccountIdentifier($username);
        $this->currentTeam = $account->getParty()->getTeam();
        $this->visit('/logout.html');
        $this->visit('/login.html');
        $this->fillField('Username', $username);
        $this->fillField('Password', $password);
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
     * @When /^i follow "([^"]*)" in the email "([^"]*)" to "([^"]*)"$/
     */
    public function iFollowInTheEmailTo($link, $subject, $email) {
        $email = $this->hasReceivedAnEmailWithTheSubject($email, $subject);
        $emailCrawler = new Crawler();
        $emailCrawler->addHtmlContent($email['body']);
        $url = $emailCrawler->selectLink($link)->attr('href');
        if (strlen($url) > 0) {
            $this->visit($url);
        }
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
     * @Then /^I log out$/
     */
    public function iLogOut() {
        $this->visit('/login.html');
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
        try {
            $driver = $this->getSession()->getDriver();
            if ($driver instanceof Selenium2Driver) {
                $filename = sprintf('%s_%s_%s.%s', $this->getMinkParameter('browser_name'), date('c'), uniqid('', true), 'png');
                $filepath = FLOW_PATH_ROOT . '/Screenshots';
                if (!is_dir($filepath)) {
                    mkdir($filepath);
                }
                file_put_contents($filepath . '/' . $filename, $this->getSession()->getScreenshot());
            }
            if ($driver instanceof GoutteDriver) {
                $filename = sprintf('%s_%s_%s.%s', 'Goutte', date('c'), uniqid('', true), 'html');
                $filepath = FLOW_PATH_ROOT . '/Screenshots';
                if (!is_dir($filepath)) {
                    mkdir($filepath);
                }
                $html = preg_replace('/<script[^>]*\/>/siu', '', $this->getSession()->getPage()->getHtml());
                $html = '<a href="' . $this->getSession()->getCurrentUrl() . '">Request Url</a><br />' . $html;
                file_put_contents($filepath . '/' . $filename, $html);
            }
        } catch(\Exception $e) {}
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
            $this->iTakeAScreenshot();
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

    /**
     * @Given /^"([^"]*)" has received an email with the subject "([^"]*)"$/
     */
    public function hasReceivedAnEmailWithTheSubject($email, $subject) {
        $emails = DebugTransport::getEmails($email);
        foreach ($emails as $email) {
            if ($email['subject'] === $subject) {
                return $email;
            }
        }
        Assert::fail('Email with the subject "' . $subject . '" for "' . $email . '"" not found');
    }

    /**
     * @Given /^the status should be "([^"]*)"$/
     */
    public function theStatusShouldBe($status) {
        $this->assertSession()->elementTextContains('css', '.currentState', $status);
    }
}
?>