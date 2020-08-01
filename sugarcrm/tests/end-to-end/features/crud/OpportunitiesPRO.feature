# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_opportunities @job7 @pro-only
Feature: Opportunities

  Background:
    Given I use default account
    Given I launch App

  @list
  Scenario: Opportunities > List View > Preview
    # Create Opportunity
    Given Opportunities records exist:
      | *name |
      | Opp_1 |
    # Create Account. (the linking part does not work)
    Given Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    Then I should see *Opp_1 in #OpportunitiesList.ListView
    Then I verify fields for *Opp_1 in #OpportunitiesList.ListView
      | fieldName | value |
      | name      | Opp_1 |
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Preview view
    Then I verify fields on #Opp_1Preview.PreviewView
      | fieldName    | value |
      | name         | Opp_1 |
      | account_name | Acc_1 |
      | sales_status | New   |

  @list-search
  Scenario: Opportunities > List View > Filter > Search main input
    Given Opportunities records exist:
      | *name | opportunity_type  | lead_source       | description       |
      | Opp_1 | Existing Business | Cold Call         | Opp_1 description |
      | Opp_2 | New Business      | Existing Customer | Opp_2 description |
      | Opp_3 | Existing Business | Self Generated    | Opp_3 description |
    Given I open about view and login
    When I choose Opportunities in modules menu
    Then I should see [*Opp_1, *Opp_2, *Opp_3] on Opportunities list view
    When I search for "Opp_2" in #OpportunitiesList.FilterView view
    Then I should see *Opp_2 in #OpportunitiesList.ListView
    Then I should not see [*Opp_1, *Opp_3] on Opportunities list view
    Then I verify fields for *Opp_2 in #OpportunitiesList.ListView
      | fieldName        | value             |
      | name             | Opp_2             |
      | lead_source      | Existing Customer |
      | opportunity_type | New Business      |

  @list-edit
  Scenario: Opportunities > List View > Inline Edit
    Given Opportunities records exist:
      | *name | opportunity_type  | lead_source | description       | amount | date_closed               |
      | Opp_1 | Existing Business | Cold Call   | Opp_1 description | 100    | 2020-10-19T19:20:22+00:00 |
    Given Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    When I click on Edit button for *Opp_1 in #OpportunitiesList.ListView
    When I set values for *Opp_1 in #OpportunitiesList.ListView
      | fieldName        | value             |
      | name             | Opp_1 edited      |
      | lead_source      | Existing Customer |
      | opportunity_type | New Business      |
    When I click on Cancel button for *Opp_1 in #OpportunitiesList.ListView
    Then I verify fields for *Opp_1 in #OpportunitiesList.ListView
      | fieldName | value |
      | name      | Opp_1 |
    When I click on Edit button for *Opp_1 in #OpportunitiesList.ListView
    When I set values for *Opp_1 in #OpportunitiesList.ListView
      | fieldName        | value             |
      | name             | Opp_1 edited      |
      | lead_source      | Existing Customer |
      | opportunity_type | New Business      |
    When I click on Save button for *Opp_1 in #OpportunitiesList.ListView
    Then I verify fields for *Opp_1 in #OpportunitiesList.ListView
      | fieldName        | value             |
      | name             | Opp_1 edited      |
      | lead_source      | Existing Customer |
      | opportunity_type | New Business      |

  @list-delete
  Scenario: Opportunities > List View > Delete
    Given Opportunities records exist:
      | *name | opportunity_type  | lead_source | description       |
      | Opp_1 | Existing Business | Cold Call   | Opp_1 description |
    Given Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    Given I open about view and login
    # Delete opportunity > Cancel
    When I choose Opportunities in modules menu
    When I click on Delete button for *Opp_1 in #OpportunitiesList.ListView
    When I Cancel confirmation alert
    Then I should see *Opp_1 in #OpportunitiesList.ListView
    # Delete opportunity > Confirm
    When I click on Delete button for *Opp_1 in #OpportunitiesList.ListView
    When I Confirm confirmation alert
    Then I should see #OpportunitiesList view
    Then I should not see *Opp_1 in #OpportunitiesList.ListView

  @delete
  Scenario: Opportunities >  Record View > Delete
    Given Opportunities records exist:
      | *name | opportunity_type  | lead_source | description       |
      | Opp_1 | Existing Business | Cold Call   | Opp_1 description |
    Given Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    When I open actions menu in #Opp_1Record
    When I choose Delete from actions menu in #Opp_1Record
    When I Cancel confirmation alert
    Then I should see #Opp_1Record view
    Then I verify fields on #Opp_1Record.HeaderView
      | fieldName | value |
      | name      | Opp_1 |
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName    | value |
      | sales_status | New   |
    When I open actions menu in #Opp_1Record
    When I choose Delete from actions menu in #Opp_1Record
    When I Confirm confirmation alert
    Then I should see #OpportunitiesList.ListView view
    Then I should not see *Opp_1 in #OpportunitiesList.ListView

  @copy
  Scenario: Opportunities > Record view > Copy
    Given Opportunities records exist:
      | *name | opportunity_type  | lead_source | description       |
      | Opp_1 | Existing Business | Cold Call   | Opp_1 description |
    Given Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    When I click show more button on #Opp_1Record view
    # Copy > Cancel
    When I open actions menu in #Opp_1Record
    When I choose Copy from actions menu in #Opp_1Record
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | name       |
      | Alex's Opp |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | lead_source       | opportunity_type |
      | Existing Customer | New Business     |
    When I click Cancel button on #OpportunitiesDrawer header
    Then I verify fields on #Opp_1Record.HeaderView
      | fieldName | value |
      | name      | Opp_1 |
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName        | value             |
      | lead_source      | Cold Call         |
      | opportunity_type | Existing Business |
    # Copy > Save
    When I open actions menu in #Opp_1Record
    When I choose Copy from actions menu in #Opp_1Record
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name       |
      | Opp_2 | Alex's Opp |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | lead_source       | opportunity_type | amount | date_closed |
      | Opp_2 | Existing Customer | New Business     | 100    | 12/12/2020  |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert
    When I click show more button on #Opp_2Record view
    Then I verify fields on #Opp_2Record.HeaderView
      | fieldName | value      |
      | name      | Alex's Opp |
    Then I verify fields on #Opp_2Record.RecordView
      | fieldName        | value             |
      | lead_source      | Existing Customer |
      | opportunity_type | New Business      |
      | amount           | $100.00           |
      | date_closed      | 12/12/2020        |

  @edit
  Scenario: Opportunities > Record View > Edit
    Given Opportunities records exist:
      | *name | opportunity_type  | lead_source | description       |
      | Opp_1 | Existing Business | Cold Call   | Opp_1 description |
    Given Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    When I click show more button on #Opp_1Record view
    # Edit > Cancel
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.HeaderView view
      | name            |
      | New Opportunity |
    When I provide input for #Opp_1Record.RecordView view
      | description        | lead_source       | opportunity_type | amount | date_closed |
      | Great Opportunity! | Existing Customer | New Business     | 100    | 12/12/2020  |
    When I click Cancel button on #Opp_1Record header
    Then I verify fields on #Opp_1Record.HeaderView
      | fieldName | value |
      | name      | Opp_1 |
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName        | value             |
      | description      | Opp_1 description |
      | lead_source      | Cold Call         |
      | opportunity_type | Existing Business |
    # Edit > Save
    When I click Edit button on #Opp_1Record header
    Then I should see #Opp_1Record view
    When I provide input for #Opp_1Record.HeaderView view
      | name            |
      | New Opportunity |
    When I provide input for #Opp_1Record.RecordView view
      | description        | lead_source       | opportunity_type | amount | date_closed |
      | Great Opportunity! | Existing Customer | New Business     | 100    | 12/12/2020  |
    When I click Save button on #Opp_1Record header
    When I close alert
    Then I verify fields on #Opp_1Record.HeaderView
      | fieldName | value           |
      | name      | New Opportunity |
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName        | value              |
      | description      | Great Opportunity! |
      | lead_source      | Existing Customer  |
      | opportunity_type | New Business       |
      | amount           | $100.00            |
      | date_closed      | 12/12/2020         |

  @create_opportunity
  Scenario: Opportunities >  Create opportunity -> Cancel/Save
    Given Accounts records exist:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    # Create new opportunity > Cancel
    When I click Create button on #OpportunitiesList header
    When I click show more button on #OpportunitiesDrawer view
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name  |
      | Opp_1 | Opp_1 |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | date_closed | amount | worst_case | best_case | account_name | sales_stage    |
      | Opp_1 | 12/12/2020  | 200    | 150        | 250       | Acc_1        | Needs Analysis |
    When I click Cancel button on #OpportunitiesDrawer header

    # Verify that no opportunity record is created
    Then I verify number of records in #OpportunitiesList.ListView is 0

    # Create new opportunity > Save
    When I click Create button on #OpportunitiesList header
    When I click show more button on #OpportunitiesDrawer view
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name  |
      | Opp_1 | Opp_1 |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | date_closed | amount | worst_case | best_case | account_name | sales_stage    | description        | lead_source       | opportunity_type | next_step |
      | Opp_1 | 12/12/2020  | 200    | 150        | 250       | Acc_1        | Needs Analysis | Great Opportunity! | Existing Customer | New Business     | Sell It!  |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Verify that Opportunity is successfully created
    Then Opportunities *Opp_1 should have the following values:
      | fieldName        | value              |
      | name             | Opp_1              |
      | best_case        | $250.00            |
      | amount           | $200.00            |
      | worst_case       | $150.00            |
      | date_closed      | 12/12/2020         |
      | sales_stage      | Needs Analysis     |
      | description      | Great Opportunity! |
      | lead_source      | Existing Customer  |
      | opportunity_type | New Business       |
      | next_step        | Sell It!           |
