Feature: Claims
  In order to work with Claims
  As a Creditor
  I need to be able to create, edit and delete claims

  Background:
    Given I imported the site "Famelo.Broensfin"
    And the following teams exist:
      | team  | username | password | firstname | lastname | email         | role                 |
      | Toni  | toni     | tester   | Toni      | Tester   | toni@foo.com  | Famelo.Saas:Customer |
      | Randy | randy    | russel   | Russel    | Randy    | randy@foo.com | Famelo.Saas:Customer |

  @fixtures @email
  Scenario: Create a new Claim
    When I am logged in as "toni" "tester"
    And I am on "/mein-konto/meine-transaktionen.html"
    And I follow "New"
    And I select "Randy" from "--form[objects][0][debtor][existing]"
    And I fill in "--form[objects][0][externalReference]" with "R1000"
    And I select "EUR" from "--form[objects][0][currency]"
    And I fill in "--form[objects][0][amount]" with "321"
    And I fill in "--form[objects][0][dueDate][date]" with "20.03.2014"
    And I fill in "--form[objects][0][creationDate][date]" with "20.02.2014"
    And I press "Submit"
    Then I should see "R1000"
    And "randy@foo.com" has received an email with the subject "New Claim"
    And I have a balance of "4.9"

  @fixtures @email
  Scenario: Create a new Claim for new Team
    When I am logged in as "toni" "tester"
    And I am on "/mein-konto/meine-transaktionen.html"
    And I follow "New"
    When I fill in "--form[objects][0][debtor][company]" with "Henry"
    And I fill in "--form[objects][0][debtor][firstname]" with "Henry"
    And I fill in "--form[objects][0][debtor][lastname]" with "Ford"
    And I fill in "--form[objects][0][debtor][email]" with "henry@foo.com"
    And I fill in "--form[objects][0][externalReference]" with "R1001"
    And I select "EUR" from "--form[objects][0][currency]"
    And I fill in "--form[objects][0][amount]" with "321"
    And I fill in "--form[objects][0][dueDate][date]" with "20.03.2014"
    And I fill in "--form[objects][0][creationDate][date]" with "20.02.2014"
    And I press "Submit"
    Then I should see "R1001"
    And I should see "Henry"
    And "henry@foo.com" has received an email with the subject "Invitation to Broensfin"
    And I have a balance of "4.9"
    When i follow "Click here to accept the Invitation." in the email "Invitation to Broensfin" to "henry@foo.com"
    Then I should see "Company Name"
    And I fill in "form-objects_0_name" with "Henry GbR"
    And I fill in "form-objects_0_users_0_name_firstName" with "Henry"
    And I fill in "form-objects_0_users_0_name_lastName" with "Ford"
    And I fill in "form-objects_0_users_0_accounts_0_accountIdentifier" with "henry"
    And I fill in "form-objects_0_users_0_accounts_0_credentialsSource" with "ford"
    And I fill in "form-objects_0_users_0_accounts_0_credentialsSource-confirmation" with "ford"
    And I fill in "form-objects_0_users_0_email" with "henry@foo.com"
    And I press "Submit"
    Then I should see "Account has been created."
    When I am on "/mein-konto/meine-transaktionen.html"
    Then I should see "Henry GbR"
    When I am logged in as "henry" "ford"
    Then I should be on page "/mein-konto.html"

  @fixtures @email
  Scenario: Creditor comments on a claim
    When the following claims exist:
      | creditor  | debtor | externalReference | currency | amount | dueDate    | creationDate |
      | Toni      | Randy  | R2001             | EUR      | 3234   | 20.03.2014 | 01.03.2014   |
      | Randy     | Toni   | R3001             | EUR      | 1234   | 1.02.2014  | 01.01.2014   |
    And I am logged in as "toni" "tester"
    Then I should be on page "/mein-konto.html"
    And I follow "My Claims"
    And I click on "Detail" on the row containing "R2001"
    Then I should see "Claim Details"
    When I fill in "--typo3_neos_expose-plugin[comment]" with "Could you please verify this?"
    And I press "Submit"
    Then I should see "Could you please verify this?"
    And "randy@foo.com" has received an email with the subject "Toni hat ein Kommentar hinterlassen"

  @fixtures @email
  Scenario: Debtor comments on a claim
    When the following claims exist:
      | creditor  | debtor | externalReference | currency | amount | dueDate    | creationDate |
      | Toni      | Randy  | R2001             | EUR      | 3234   | 20.03.2014 | 01.03.2014   |
      | Randy     | Toni   | R3001             | EUR      | 1234   | 1.02.2014  | 01.01.2014   |
    And I am logged in as "randy" "russel"
    Then I should be on page "/mein-konto.html"
    And I follow "My Debts"
    And I click on "Detail" on the row containing "R2001"
    Then I should see "Claim Details"
    When I fill in "--typo3_neos_expose-plugin[comment]" with "Really?"
    And I press "Submit"
    Then I should see "Really?"
    And "toni@foo.com" has received an email with the subject "Randy hat ein Kommentar hinterlassen"

  @fixtures @email
  Scenario: Reject a claim
    When the following claims exist:
      | creditor  | debtor | externalReference | currency | amount | dueDate    | creationDate |
      | Toni      | Randy  | R2001             | EUR      | 3234   | 20.03.2014 | 01.03.2014   |
      | Randy     | Toni   | R3001             | EUR      | 1234   | 1.02.2014  | 01.01.2014   |
    And I am logged in as "toni" "tester"
    Then I should be on page "/mein-konto.html"
    And I follow "My Debts"
    And I click on "Detail" on the row containing "R3001"
    Then I should see "Claim Details"
    And the status should be "pending"
    When I press "Reject"
    Then the status should be "rejected"
    And "randy@foo.com" has received an email with the subject "Toni has updated a claim"

  @fixtures @email
  Scenario: Accept a claim
    When the following claims exist:
      | creditor  | debtor | externalReference | currency | amount | dueDate    | creationDate |
      | Toni      | Randy  | R2001             | EUR      | 3234   | 20.03.2014 | 01.03.2014   |
      | Randy     | Toni   | R3001             | EUR      | 1234   | 1.02.2014  | 01.01.2014   |
    And I am logged in as "toni" "tester"
    Then I should be on page "/mein-konto.html"
    And I follow "My Debts"
    And I click on "Detail" on the row containing "R3001"
    Then I should see "Claim Details"
    And the status should be "pending"
    When I press "Accept"
    Then the status should be "accepted"
    And "randy@foo.com" has received an email with the subject "Toni has updated a claim"
