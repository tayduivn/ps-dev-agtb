# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@portal
Feature: Portal automation

  Background:
    Given I am logged in

  @ci-excluded
  Scenario Outline: Portal Automation > Create Portal user and login
    # Generate account record
    Given Accounts records exist:
      | *   | name           |
      | A_1 | Portal Account |

    # Create New Contact record with Portal access
    # Portal needs to be activated in Admin > Sugar Portal
    When I choose Contacts in modules menu
    When I click Create button on #ContactsList header
    When I click show more button on #ContactsRecord view
    When I provide input for #ContactsDrawer.HeaderView view
      | *   | first_name | last_name |
      | C_1 | Alex       | Nisevich  |
    When I provide input for #ContactsDrawer.RecordView view
      | *   | description | portal_active | portal_name   | portal_password   | account_name   |
      | C_1 | Portal user | true          | <portal_name> | <portal_password> | Portal Account |
    When I Confirm confirmation alert
    When I click Save button on #ContactsDrawer header
    When I close alert
    Then I should see #ContactsList.ListView view

    # Update portal login password for existing contact
    When I select *C_1 in #ContactsList.ListView
    When I click Edit button on #C_1Record header
    When I provide input for #C_1Record.RecordView view
      | portal_password           |
      | <portal_password_changed> |
    When I click Save button on #C_1Record header
    When I close alert
    Then I should see #C_1Record view

    # Open Portal in a new tab
    When I open new browser tab and navigate to "portal/index.html" url
    When I wait for 2 seconds

    # TODO: This part of the test fails. Re-enable this section after AT-288 is fixed
#    And I switch to tab 1

#    # Login to Portal
#    When I login to Portal with the following credentials in #Login:
#      | portal_name   | portal_password           |
#      | <portal_name> | <portal_password_changed> |
    Examples:
      | portal_name | portal_password | portal_password_changed |
      | AlexN       | Sugar123        | Sugar321                |
