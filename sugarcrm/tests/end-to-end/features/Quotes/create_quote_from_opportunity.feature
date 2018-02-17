# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@quotes
Feature: Generate Quote From RLI subpanel in Opportunity record view

  Background:
    Given I use default account
    Given I launch App

    @GenerateQuote_from_RLIs_in_RLISubpane_of_OpportunityRecordView
    Scenario: Opportunity Record View > RLI Subpanel > Generate Quote From Multiple RLIs
      Given Accounts records exist:
        | name  |
        | Acc_1 |
      Given I open about view and login
      When I choose Opportunities in modules menu
      When I click Create button on #OpportunitiesList header
      When I provide input for #OpportunitiesDrawer.HeaderView view
        | *     | name                  |
        | Opp_A | CreateOpportunityTest |
      When I provide input for #OpportunitiesDrawer.RecordView view
        | *     | account_name |
        | Opp_A | Acc_1        |
        # Provide input for the first (default) RLI
      When I provide input for #OpportunityDrawer.RLITable view for 1 row
        | *     | name | date_closed | best_case | sales_stage   | quantity | likely_case |
        | RLI_1 | RLI1 | 12/12/2020  | 300       | Qualification | 5        | 200         |
        # Add second RLI by clicking '+' button on the first row
      When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
        # Provide input for the second RLI
      When I provide input for #OpportunityDrawer.RLITable view for 2 row
        | *     | name | date_closed | best_case | sales_stage   | quantity | likely_case |
        | RLI_2 | RLI2 | 12/12/2021  | 500       | Qualification | 10       | 400         |
        # Add third RLI by clicking '+' button on the second row
      When I choose addRLI on #OpportunityDrawer.RLITable view for 2 row
        # Provide input for the third RLI
      When I provide input for #OpportunityDrawer.RLITable view for 3 row
        | *     | name | date_closed | best_case | sales_stage   | quantity | likely_case |
        | RLI_3 | RLI3 | 12/12/2022  | 50        | Qualification | 10       | 40          |
        # Save new opportunity
      When I click Save button on #OpportunitiesDrawer header
      When I close alert

      When I select *Opp_A in #OpportunitiesList.ListView
      When I open the revenuelineitems subpanel on #Opp_ARecord view
      When I toggleAll records in #Opp_ARecord.SubpanelsLayout.subpanels.revenuelineitems
      When I select GenerateQuote action in #Opp_ARecord.SubpanelsLayout.subpanels.revenuelineitems
        # Complete quote record and save
      When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
      When I provide input for #QuotesRecord.HeaderView view
        | *      | name  |
        | Quote1 | Alex2 |
      When I provide input for #QuotesRecord.RecordView view
        | *      | date_quote_expected_closed |
        | Quote1 | 12/12/2020                 |
      Then I verify fields on #QuotesRecord.RecordView
        | fieldName            | value |
        | billing_account_name | Acc_1 |
      When I click Save button on #QuotesRecord header
      When I close alert
        # Verify data in Quotes subpanel of Opportunity record view
      When I choose Opportunities in modules menu
      When I select *Opp_A in #OpportunitiesList.ListView
      When I open the quotes subpanel on #Opp_ARecord view
      Then I verify fields for *Quote1 in #Opp_ARecord.SubpanelsLayout.subpanels.quotes
        | fieldName                  | value      |
        | name                       | Alex2      |
        | total_usdollar             | $640.00    |
        | date_quote_expected_closed | 12/12/2020 |


    @delete_RLIs_from_RLISubpane_of_OpportunityRecordView
    Scenario: Opportunity Record View > RLI Subpanel > Delete All RLIs
      Given Accounts records exist:
        | name  |
        | Acc_1 |
      Given I open about view and login
      When I choose Opportunities in modules menu
      When I click Create button on #OpportunitiesList header
      When I provide input for #OpportunitiesDrawer.HeaderView view
        | *     | name                  |
        | Opp_A | CreateOpportunityTest |
      When I provide input for #OpportunitiesDrawer.RecordView view
        | *     | account_name |
        | Opp_A | Acc_1        |
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

      When I select *Opp_A in #OpportunitiesList.ListView
      When I open the revenuelineitems subpanel on #Opp_ARecord view
      When I toggleAll records in #Opp_ARecord.SubpanelsLayout.subpanels.revenuelineitems
        #When I select GenerateQuote action in #Opp_ARecord.SubpanelsLayout.subpanels.revenuelineitems
      When I select Delete action in #Opp_ARecord.SubpanelsLayout.subpanels.revenuelineitems
      When I Confirm confirmation alert
      When I close alert
      Then I verify fields on #Opp_ARecord.RecordView
        | fieldName  | value |
        | amount     | $0.00 |
        | best_case  | $0.00 |
        | worst_case | $0.00 |

