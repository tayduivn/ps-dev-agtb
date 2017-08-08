# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @Opportunity
Feature: RLI module verification

  Background:
    Given I use default account
    Given I launch App

  @T_27794
  Scenario: Opportunities >  Verify that RLIs with closed lost sales stage are not included in the Opportunity rollup total
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | *name |
      | Opp_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I open actions menu in #RLI_1Record
    When I click Edit button on #RLI_1Record header
    When I click show more button on #RLI_1Record view
    When I provide input for #RLI_1Record.RecordView view
      | sales_stage |
      | Closed Lost |
    When I click Save button on #RLI_1Record header
    When I close alert
    # Verify that RLI's amount is not rolled into opportunity if RLI's sales stage is closed lost
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Record view
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName  | value |
      | amount     | $0.00 |
      | best_case  | $0.00 |
      | worst_case | $0.00 |

  @T_26017
  Scenario Outline: Opportunities > Verify that Status of the opportunity is changed to closed won/lost if all RLIs linked to the opportunity have sales stage "Close won/lost"
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | *name |
      | Opp_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I open actions menu in #RLI_1Record
    When I click Edit button on #RLI_1Record header
    When I click show more button on #RLI_1Record view
    When I provide input for #RLI_1Record.RecordView view
      | sales_stage     |
      | <rliSalesStage> |
    When I click Save button on #RLI_1Record header
    When I close alert
      # Verify that Oportunity's status depends on sales stage of linked RLIs
    When I choose Opportunities in modules menu
    Then I should see *Opp_1 in #OpportunitiesList.ListView
    Then I verify fields for *Opp_1 in #OpportunitiesList.ListView
      | fieldName    | value       |
      | sales_status | <oppStatus> |
    Examples:
      | rliSalesStage | oppStatus   |
      | Closed Won    | Closed Won  |
      | Closed Lost   | Closed Lost |
      | Qualification | In Progress |

  @T_18030
  Scenario Outline: Opportunities > Verify that changing account on opportunity should cascade down to all RLIs linked to this opportunity
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | *name |
      | Opp_1 |
    Given Accounts records exist:
      | *name            |
      | <First_Account>  |
      | <Second_Account> |

    Given I open about view and login
    #Select account record for opportunity
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Record view
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | account_name    |
      | <First_Account> |
    When I click Save button on #Opp_1Record header
    When I close alert

   # Verify that RLI's account is updated
    When I choose RevenueLineItems in modules menu
    Then I should see *RLI_1 in #RevenueLineItemsList.ListView
    When I click on preview button on *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Preview view
    Then I verify fields on #RLI_1Preview.PreviewView
      | fieldName    | value           |
      | account_name | <First_Account> |

    #Select another account for opportunity
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Record view
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | account_name     |
      | <Second_Account> |
    When I click Save button on #Opp_1Record header
    When I close alert

   # Verify that RLI's account is updated
    When I choose RevenueLineItems in modules menu
    Then I should see *RLI_1 in #RevenueLineItemsList.ListView
    When I click on preview button on *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Preview view
    Then I verify fields on #RLI_1Preview.PreviewView
      | fieldName    | value            |
      | account_name | <Second_Account> |
    Examples:
      | First_Account | Second_Account |
      | Account_1     | #@##_acc_%^&   |

  @T_18905
  Scenario Outline: Opportunities > Verify Opportunity cannot be deleted in the record view if sales stage of one or more RLIs is closed won
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage        | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | <closedSalesStage> | 5        |
    Given Opportunities records exist related via opportunities link:
      | *name |
      | Opp_1 |
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    Given I open about view and login
    # Select account record for opportunity
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Record view
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | account_name |
      | Acc_1        |
    When I click Save button on #Opp_1Record header
    When I close alert
    # Verify that Delete menu item is disabled
    When I open actions menu in #Opp_1Record and check:
      | menu_item | active |
      | Delete    | false  |
    # Change RLI sales stage to any but not Closed
    When I choose RevenueLineItems in modules menu
    Then I should see *RLI_1 in #RevenueLineItemsList.ListView
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | sales_stage       |
      | <otherSalesStage> |
    When I click Save button on #RLI_1Record header
    When I close alert

    # Verify that now Delete menu item is active
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Record view
    When I open actions menu in #Opp_1Record and check:
      | menu_item | active |
      | Delete    | true   |
    Examples:
      | closedSalesStage | otherSalesStage |
      | Closed Won       | Needs Analysis  |
      | Closed Lost      | Prospecting     |
