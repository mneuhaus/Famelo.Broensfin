Feature: Customer Login
  In order to access the Broensfin application
  As a customer of the Broensfin
  I need a way to authenticate

  @fixtures
  Scenario: Login to customer area
    Given I imported the site "Famelo.Broensfin"
    And I am not authenticated
    And the following teams exist:
      | team  | username | password | firstname | lastname | email         | role                 |
      | Toni  | toni     | tester   | Toni      | Tester   | toni@foo.com  | Famelo.Saas:Customer |
    When I go to "/de/login.html"
    Then I should see a login form
    When I fill in "Username" with "toni"
    And I fill in "Password" with "tester"
    And I press "Login"
    And I wait 3 secs
    Then I should be logged in
    And I should be on page "/de/mein-konto.html"

  @fixtures
  Scenario: Login attempt with wrong password
    Given I imported the site "Famelo.Broensfin"
    And I am not authenticated
    And the following teams exist:
      | team  | username | password | firstname | lastname | email         | role                 |
      | Toni  | toni     | tester   | Toni      | Tester   | toni@foo.com  | Famelo.Saas:Customer |
    When I go to "/de/login.html"
    And I fill in "Username" with "toni"
    And I fill in "Password" with "wrong-password"
    And I press "Login"
    And I wait 3 secs
    Then I should be see a flash message "Wrong username or password."