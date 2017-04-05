Feature: Login

  @login
  Scenario: Login and logout
    Given I am on "/"
    When I wait until the loading is completed
    And I fill in "admin" for "username"
    And I fill in "admin" for "password"
    And I press "login_button"
    And I wait until the loading is completed
    Then I should not see "You must specify a valid username and password."
    Then I should see "My Dashboard"
    When I click "#userList"
    And I click ".profileactions-logout"