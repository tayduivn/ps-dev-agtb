# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_accounts
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
      | amount       | $0.00 |
      | best_case    | $0.00 |
      | worst_case   | $0.00 |
      | sales_status | New   |


  @create_opportunity @pr
  Scenario: Opportunities >  Create opportunity with RLIs
    Given Accounts records exist:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    When I click Create button on #OpportunitiesList header
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *        | name                  |
      | RecordID | CreateOpportunityTest |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *        | account_name |
      | RecordID | Acc_1        |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | name | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI1 | 12/12/2020  | 300       | Qualification | 5        | 200         |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    When I provide input for #OpportunityDrawer.RLITable view for 2 row
      | name | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI2 | 12/12/2021  | 500       | Qualification | 10       | 400         |
    # Add third RLI by clicking '+' button on the second row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 2 row
    # Provide input for the third RLI
    When I provide input for #OpportunityDrawer.RLITable view for 3 row
      | name | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI3 | 12/12/2022  | 50        | Qualification | 10       | 40          |
    # Remove first RLI
    When I choose removeRLI on #OpportunityDrawer.RLITable view for 1 row
    # Save new opportunity
    When I click Save button on #OpportunitiesDrawer header
    When I close alert
    # Verify data
    When I click on preview button on *RecordID in #OpportunitiesList.ListView
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName    | value                 |
      | name         | CreateOpportunityTest |
      | best_case    | $550.00               |
      | amount       | $440.00               |
      | worst_case   | $440.00               |
      | date_closed  | 12/12/2022            |
      | sales_status | In Progress           |
