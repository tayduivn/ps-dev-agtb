# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @rli
Feature: RLI module verification

  Background:
    Given I use default account
    Given I launch App

  @T_17776
  Scenario: RLI > Select Product
    # Create Product
    Given ProductTemplates records exist:
      | *name     | discount_price | list_price | cost_price |
      | Product_1 | 1000           | 2000       | 500        |
    # Could not related Product Category to Product Template via API
    Given ProductCategories records exist related via category_link link:
      | name       |
      | Category_1 |
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 300         | 300       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |

    #Relate ProductTemplate to ProductCaregory via UI
    Given I open about view and login
    #TODO: remove this section after setting relation via API is fixed
    When I go to "ProductTemplates" url
    Then I should see *Product_1 in #ProductTemplatesList.ListView
    When I select *Product_1 in #ProductTemplatesList.ListView
    Then I should see #Product_1Record view
    When I click Edit button on #Product_1Record header
    When I provide input for #Product_1Record.RecordView view
      | category_name |
      | Category_1    |
    When I click Save button on #Product_1Record header
    When I close alert
    Then I verify fields on #Product_1Record.RecordView
      | fieldName     | value      |
      | category_name | Category_1 |

    # Select product when editing RLI
    When I choose RevenueLineItems in modules menu
    Then I should see *RLI_1 in #RevenueLineItemsList.ListView
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | product_template_name |
      | Product_1             |
    When I click Save button on #RLI_1Record header
    When I close alert
    When I click show more button on #RLI_1Record view
    # Verify that Product info is applied toRLI record
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName      | value      |
      | discount_price | $1,000.00  |
      | total_amount   | $5,000.00  |
      | category_name  | Category_1 |
      | list_price     | $2,000.00  |
      | cost_price     | $500.00    |


  @T_19044
  Scenario: RLI > Select Product Category
    # Could not related Product Category to Product Template via API
    Given ProductCategories records exist:
      | name       |
      | Category_1 |
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 300         | 300       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    # Select product category when editing RLI
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    Then I should see *RLI_1 in #RevenueLineItemsList.ListView
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | category_name |
      | Category_1    |
    When I click Save button on #RLI_1Record header
    When I close alert
    When I open actions menu in #RLI_1Record
    * I choose GenerateQuote from actions menu in #RLI_1Record
    When I close alert
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName             | value |
      | product_template_name |       |


  @T_26018
  Scenario Outline: RevenueLineItems > Record View > Set Sales Satge to Closed Won/Lost
    # Create RLI record
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I open actions menu in #RLI_1Record
    When I click Edit button on #RLI_1Record header
    When I click show more button on #RLI_1Record view
    When I provide input for #RLI_1Record.RecordView view
      | sales_stage  |
      | <salesStage> |
    When I click Save button on #RLI_1Record header
    When I click show more button on #RLI_1Record view
    When I close alert
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName   | value          |
      | likely_case | <likelyAmount> |
      | best_case   | <likelyAmount> |
      | worst_case  | <likelyAmount> |
    Examples:
      | salesStage  | likelyAmount |
      | Closed Won  | $300.00      |
      | Closed Lost | $300.00      |


  @T_17779
  Scenario: RLI > Select Opportunity
    # Create RLI
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 3000        | 3000      | Prospecting | 3        |
    # Create Opportunity
    Given Opportunities records exist:
      | name  |
      | Opp_1 |
    # Link Account
    Given Accounts records exist related via accounts link:
      | *name  |
      | Acc_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    # Manually add account info to Opportunity
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | opportunity_name |
      | Opp_1            |
    # Verify Account name is populated in edit mode
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName    | value |
      | account_name | Acc_1 |
    When I click Save button on #RLI_1Record header
    When I close alert
    # Verify account name is populated in RLI record view
    Then I should see #RLI_1Record view
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName    | value |
      | account_name | Acc_1 |
    When I click account_name field on #RLI_1Record.RecordView view
    Then I should see #Acc_1Record.RecordView view
