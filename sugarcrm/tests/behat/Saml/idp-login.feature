Feature: SAML IDP-initiated login
  When I log in to my SAML provider
  I want to be able to open SugarCRM instantly using link/button on IdP page

  @login @saml
  Scenario: SAML IDP-initated login
    # Given I use logoutFlowWithRedirectBinding SAML configuration
    Given I am on "http://localhost:8080/simplesaml/saml2/idp/SSOService.php?spentityid=logoutFlowWithRedirectBinding"
    When I wait for the page to be loaded
    Then I should see "Enter your username and password"
    When I fill in "user1" for "username"
    And I fill in "user1pass" for "password"
    And I press "Login"
    Then I should be redirected to "/index.php"
    When I wait until the loading is completed
    Then I should not see "You must specify a valid username and password."
    When I skip login wizard
    Then I should see "My Dashboard"
    When I click "#userList"
    And I click ".profileactions-logout"