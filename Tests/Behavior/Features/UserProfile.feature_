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