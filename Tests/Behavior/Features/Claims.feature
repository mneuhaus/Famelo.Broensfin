Feature: Claims
  In order to work with Claims
  As a Creditor
  I need to be able to create, edit and delete claims

  @fixtures
  Scenario: Create a new Claim
    Given I am logged in as a customer
    And I am on "/mein-konto/meine-transaktionen.html"
    And the following teams exist:
      | team  | username | password | firstname | lastname | email         | role                 |
      | Toni  | toni     | tester   | Toni      | Tester   | toni@foo.com  | Famelo.Saas:Customer |
      | Randy | russel   | russel   | Russel    | Randy    | randy@foo.com | Famelo.Saas:Customer |
    When I press "btn-add"
    And I i search for the team "Randy"
    And I fill in "--form[objects][0][externalReference]" with "R1000"
    And I select "EUR" from "--form[objects][0][currency]"
    And I fill in "--form[objects][0][amount]" with "321"
    And I fill in "--form[objects][0][dueDate]" with "20.03.2014"
    And I fill in "--form[objects][0][creationDate]" with "20.02.2014"
    And I press "Create"
    Then I should have a claim with the reference "R32342"
    And "randy@foo.com" has received an email with the subject "New Claim"

  @fixtures
  Scenario: Create a new Claim for new Team
    Given I am logged in as a customer
    And I am on "/mein-konto/meine-transaktionen.html"
    And the following teams exist:
      | team  | username | password | firstname | lastname | email         | role                 |
      | Toni  | toni     | tester   | Toni      | Tester   | toni@foo.com  | Famelo.Saas:Customer |
      | Randy | russel   | russel   | Russel    | Randy    | randy@foo.com | Famelo.Saas:Customer |
    When I press "btn-add"
    And I i search for the team "Randy"
    Then i should see the invite form
    When I fill in "--form[objects][0][debtor][company]" with "Randy"
    And I fill in "--form[objects][0][debtor][firstName]" with "Randy"
    And I fill in "--form[objects][0][debtor][lastname]" with "Russel"
    And I fill in "--form[objects][0][debtor][email]" with "randy@foo.com"
    And I fill in "--form[objects][0][externalReference]" with "R1001"
    And I select "EUR" from "--form[objects][0][currency]"
    And I fill in "--form[objects][0][amount]" with "321"
    And I fill in "--form[objects][0][dueDate]" with "20.03.2014"
    And I fill in "--form[objects][0][creationDate]" with "20.02.2014"
    And I press "Create"
    Then I should have a claim with the reference "R1001"
    Then "randy@foo.com" has received an email with the subject "Invite to Broensfin"