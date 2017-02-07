# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules @accounts-group
Feature: Accounts module verification

  Background:
    Given I use default account
    Given I launch App with config: "skipTutorial"

  @list-method
  Scenario: Accounts > List View > Contain pre-created record
    Given Accounts records exist:
      | *name     | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *Account_A in #AccountsList
    Then I verify fields for *Account_A in #AccountsList:
      | fieldName               | value     |
      | name                    | Account_A |

  @list-search-method
  Scenario: Accounts > List View > Filter > Search main input
    Given Accounts records exist:
      | *name          | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_Search | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see #AccountsList view
    When I search for "Account_Search" in #AccountsList:FilterView view
    Then I should see *Account_Search in #AccountsList
    Then I verify fields for *Account_Search in #AccountsList:
      | fieldName               | value          |
      | name                    | Account_Search |

  @delete-method
  Scenario: Accounts > Delete > Delete account from Record View
    Given Accounts records exist:
      | *name     | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    When I select *Account_A in #AccountsList
    Then I should see #Account_ARecord view
    When I open actions menu in #Account_ARecord
    * I choose Delete from actions menu in #Account_ARecord
    When I Confirm confirmation alert
    Then I should see #AccountsList view
    Then I should not see *Account_A in #AccountsList
