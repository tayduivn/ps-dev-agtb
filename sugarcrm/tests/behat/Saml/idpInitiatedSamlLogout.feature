@saml @idp
Feature: Check SAML IdP initiated log out
  Log in as SAML user and check SAML IdP initiated log out

  @logout
  Scenario: Check SAML IdP initiated log out
    Given I am on "/"
    When I wait until the loading is completed
    And I wait until login popup is opened
    And I switch to login popup
    And I wait until the loading is completed
    Then I should see "Enter your username and password"
    When I fill in "username" with "user1"
    And I fill in "password" with "user1pass"
    And I press "Login"
    And I switch to main window
    And I wait for element ".profileactions-logout"
    And I initiate SAML logout
    And I wait until login popup is opened
    And I switch to login popup
    And I wait until the loading is completed
    Then I should see "Enter your username and password"
    