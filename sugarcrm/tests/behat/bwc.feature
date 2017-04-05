Feature: bwc

  @bwc
  Scenario: Testing BWC and sidecar
    Given I am on "/"
    When I wait until the loading is completed
    And I fill in "admin" for "username"
    And I fill in "admin" for "password"
    And I press "login_button"
    And I wait until the loading is completed
    Then I should not see "You must specify a valid username and password."
    Then I should see "My Dashboard"
    When I click "#userList"
    And I click ".administration"
    When I wait until the loading is completed
    And wait for the page to be loaded
    And I switch to BWC
    And I follow "Password Management"
    Then I should see "Password Requirements"
    Then I should see "LDAP Support"
    Then I should see "SAML Authentication"
    When I switch to sidecar
    And I follow "Accounts"
    When I wait until the loading is completed
    Then I should see "Accounts"
    Then I should see "My Accounts"
    When I click "#userList"
    And I click ".profileactions-logout"