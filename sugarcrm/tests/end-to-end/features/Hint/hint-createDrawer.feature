# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @hint @ci-excluded
Feature: Hint - Create Drawer use cases verification

  Background:
    Given I use default account
    Given I launch App with config: ""

  @leads/contacts-drawer-hintview
  Scenario Outline: Leads > Create Drawer View > Preview > Check fields
    Given I open about view and login
    When I choose <module> in modules menu
    When I click Create button on #<module>List header
    When I provide input for #<module>Drawer.HeaderView view
      | first_name  | *last_name |
      | <firstName> | <lastName> |
    When I provide input for #<module>Drawer.RecordView view
      | email   |
      | <eMail> |
    When I click Save button on #<module>Drawer header
    When I click on preview button on *<lastName> in #<module>List.ListView
    Then I should see #<lastName>Preview view
    Then I should see Logo on #<lastName>Preview.EnrichedView
    Then I verify fields on #<lastName>Preview.EnrichedView
      | fieldName               | value                                      |
      | full_name               | <firstName> <lastName>                     |
      | email                   | <eMail>                                    |
      | account_name            | SugarCRM                                   |
      | hint_account_location_c | 10050 N Wolfe Rd, Cupertino, CA 95014, USA |
    Then I should see News on #<lastName>Preview.NewsView
    Examples:
      | module   | lastName | firstName | eMail               |
      | Leads    | Oram     | Clint     | clint@sugarcrm.com  |
      | Contacts | Green    | Rich      | rgreen@sugarcrm.com |
