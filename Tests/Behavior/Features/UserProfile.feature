Feature: UserProfile
  In order to update my information
  As a Customer
  I need a form to update my information

  Background:
    Given I imported the site "Famelo.Broensfin"
    And the following teams exist:
      | team  | username | password | firstname | lastname | email         | role                 |
      | Toni  | toni     | tester   | Toni      | Tester   | toni@foo.com  | Famelo.Saas:Customer |

  @fixtures
  Scenario: Update Information
    When I am logged in as "toni" "tester"
    And I am on "/mein-konto/meine-profil.html"
    Then I should see "My Profile"

  @fixtures
  Scenario: Change password
    Given I am logged in as "toni" "tester"
    And I am on "/mein-konto/change-password.html"
    When I fill in "form-objects_0_users_0_accounts_0_credentialsSource" with "newPassword"
    And I fill in "form-objects_0_users_0_accounts_0_credentialsSource-confirmation" with "newPassword"
    And I press "Absenden"
    Then I should be able to log in with "toni" "newPassword"
