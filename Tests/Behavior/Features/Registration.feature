Feature: Registration
  In order to access the Broensfin application
  As a customer of the Broensfin
  I need a way to register a new account

  @fixtures
  Scenario: Register a new User
    Given I imported the site "Famelo.Broensfin"
    And I am not authenticated
    When I go to "/de/anmelden.html"
    And I wait 3 secs
    And I fill in "form-objects_0_name" with "Toni GbR"
    And I fill in "form-objects_0_users_0_name_firstName" with "Toni"
    And I fill in "form-objects_0_users_0_name_lastName" with "Tester"
    And I fill in "form-objects_0_users_0_accounts_0_accountIdentifier" with "toni"
    And I fill in "form-objects_0_users_0_accounts_0_credentialsSource" with "tester"
    And I fill in "form-objects_0_users_0_accounts_0_credentialsSource-confirmation" with "tester"
    And I fill in "form-objects_0_users_0_email" with "apocalip+toni@gmail.com"
    And I press "Absenden"
    Then I should be on page "/de/login.html"
    And I should be see a flash message "Account has been created."
    And I should find a new Team "Toni GbR"
    And The team "Toni GbR" should have a subscription to the "Deposit" Plan
    And The team "Toni GbR" should have a "EUR" as default currency
    And The team "Toni GbR" should have a balance of "5"

  Scenario: Login to dashboard
    Given I imported the site "Famelo.Broensfin"
    And I am not authenticated
    When I go to "/de/login.html"
    And I fill in "Username" with "toni"
    And I fill in "Password" with "tester"
    And I press "Login"
    And I wait 5 secs
    Then I should be logged in
    And I should be on page "/de/mein-konto.html"
    And I should see my Transactions