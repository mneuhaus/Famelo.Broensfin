Feature: Customer Login
  In order to access the Broensfin application
  As a customer of the Broensfin
  I need a way to authenticate

  Scenario: Show login form for not authenticated user
    Given I imported the site "Famelo.Broensfin"
    And I am not authenticated
    When I go to "/login.html"
    Then I should see a login form

  @fixtures
  Scenario: Login to backend with different roles
    Given I imported the site "Famelo.Broensfin"
    And I am not authenticated
    And the following teams exist:
      | team | username | password | firstname | lastname | role                |
      | Toni | toni     | tester   | Toni      | Tester   | Famelo.Saas:Customer |
    When I go to "/login.html"
    And I fill in "Username" with "toni"
    And I fill in "Password" with "tester"
    And I press "Login"
    Then I should be logged in
    And I should be on page "mein-konto.html"
