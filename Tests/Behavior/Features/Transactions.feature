Feature: Transactions
  In order to use broensfin's features
  As a customer
  I need a way to deposit money into my account

  Background:
    Given I imported the site "Famelo.Broensfin"
    And the following teams exist:
      | team  | username | password | firstname | lastname | email         | role                 |
      | Toni  | toni     | tester   | Toni      | Tester   | toni@foo.com  | Famelo.Saas:Customer |
      | Randy | russel   | russel   | Russel    | Randy    | randy@foo.com | Famelo.Saas:Customer |

  @fixtures
  Scenario: Login to customer area
    And I am not authenticated
    When I go to "/de/login.html"
    And I fill in "Username" with "toni"
    And I fill in "Password" with "tester"
    And I press "Login"
    Then I should be logged in
    And I should be on page "/de/mein-konto.html"

  @fixtures @email
  Scenario: Buy more credit
    Given I am logged in as "toni" "tester"
    And I have a balance of "5"
    When I follow "Credits kaufen"
    And I select "10" from "--famelo_saas-transactions[amount]"
    And I select "Invoice" from "--famelo_saas-transactions[paymentGateway]"
    And I press "Jetzt kaufen"
    Then I should be on page "/de/mein-konto.html"
    And I should be see a flash message "Payment successful"
    And I have a balance of "15"
    And the last transaction amount is "10"
    And "toni@foo.com" has received an email with the subject "Rechnung"

  # @fixtures
  # Scenario: Basic EUR transaction
  #   Given I am logged in as "toni" "tester"
  #   And I have a balance of "5"
  #   When i execute an action that costs "0.1" "EUR"
  #   Then I have a balance of "4.9"

  # @fixtures
  # Scenario: Basic Point transaction
  #   Given I am logged in as "toni" "tester"
  #   And I have a balance of "5"
  #   When i execute an action that costs "1" "POINT"
  #   Then I have a balance of "4.9"
