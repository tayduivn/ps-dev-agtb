# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @hint @ci-excluded
Feature: Hint - Preview use cases verification

  Background:
    Given I use default account
    Given I launch App with config: ""

  @leads/contacts-list-hintview
  Scenario Outline: Leads/Contacts > List View > Preview > Check fields
    Given <module> records exist:
      | *last_name | first_name  | email             |
      | <lastName> | <firstName> | <eMail> (primary) |
    Given I open about view and login
    When I choose <module> in modules menu
    When I click on preview button on *<lastName> in #<module>List.ListView
    Then I should see #<lastName>Preview view
    Then I should see Logo on #<lastName>Preview.EnrichedView
    Then I verify fields on #<lastName>Preview.EnrichedView
      | fieldName           | value                  |
      | full_name           | <firstName> <lastName> |
      | email               | <eMail>                |
      | account_name        | SugarCRM               |
    Then I should see News on #<lastName>Preview.NewsView
    Examples:
      | module   | lastName | firstName | eMail               |
      | Leads    | Green    | Rich      | rgreen@sugarcrm.com |
      | Contacts | Augustin | Larry     | lma@sugarcrm.com    |
