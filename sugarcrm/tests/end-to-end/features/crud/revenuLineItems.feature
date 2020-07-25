# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_revenueLineItems @job6 @ent-only
Feature: Revenue Line Items module verification

  Background:
    Given I use default account
    Given I launch App

  @list-preview
  Scenario Outline: Revenue Line Items > List View > Preview
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity   |
      | RLI_1 | 2020-10-19T19:20:22+00:00 | <likely>    | <best>    | Prospecting | <quantity> |
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    Then I should see *RLI_1 in #RevenueLineItemsList.ListView
    Then I verify fields for *RLI_1 in #RevenueLineItemsList.ListView
      | fieldName | value |
      | name      | RLI_1 |
    When I click on preview button on *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Preview view
    When I click show more button on #RLI_1Preview view
    Then I verify fields on #RLI_1Preview.PreviewView
      | fieldName        | value                |
      | name             | RLI_1                |
      | opportunity_name | Opp_1                |
      | date_closed      | 10/19/2020           |
      | likely_case      | <likelyFormatted>    |
      | best_case        | <bestFormatted>      |
      | sales_stage      | Prospecting          |
      | probability      | 10                   |
      | discount_price   | <unitPriceFormatted> |
      | quantity         | <quantity>           |
      | total_amount     | <likelyFormatted>    |
    When I click show less button on #RLI_1Preview view

    Examples:
      | likely | likelyFormatted | best | bestFormatted | unitPriceFormatted | quantity |
      | 200    | $200.00         | 2000 | $2,000.00     | $40.00             | 5.00     |
      | 400    | $400.00         | 4000 | $4,000.00     | $10.00             | 40.00    |

  @list-search
  Scenario: Revenue Line Items > List View > Filter > Search main input
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2020-10-19T19:20:22+00:00 | 200         | 300       | Prospecting | 1        |
      | RLI_2 | 2020-10-20T19:20:22+00:00 | 300         | 400       | Prospecting | 2        |
      | RLI_3 | 2020-10-21T19:20:22+00:00 | 400         | 500       | Prospecting | 3        |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    Then I should see #RevenueLineItemsList.ListView view
    When I search for "RLI_2" in #RevenueLineItemsList.FilterView view
    Then I should not see *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see *RLI_2 in #RevenueLineItemsList.ListView
    Then I should not see *RLI_3 in #RevenueLineItemsList.ListView
    Then I verify fields for *RLI_2 in #RevenueLineItemsList.ListView
      | fieldName   | value      |
      | name        | RLI_2      |
      | date_closed | 10/20/2020 |

  @list-edit @T_34547
  Scenario: Revenue Line Items > List View > Inline Edit
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2020-10-19T19:20:22+00:00 | 300         | 300       | Prospecting | 1.5      |
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I click on Edit button for *RLI_1 in #RevenueLineItemsList.ListView
    When I set values for *RLI_1 in #RevenueLineItemsList.ListView
      | fieldName | value        |
      | name      | RLI_1 edited |
    When I click on Cancel button for *RLI_1 in #RevenueLineItemsList.ListView
    Then I verify fields for *RLI_1 in #RevenueLineItemsList.ListView
      | fieldName | value |
      | name      | RLI_1 |
    When I click on Edit button for *RLI_1 in #RevenueLineItemsList.ListView
    When I set values for *RLI_1 in #RevenueLineItemsList.ListView
      | fieldName   | value        |
      | name        | RLI_1 edited |
      | date_closed | 12/12/2018   |
    When I click on Save button for *RLI_1 in #RevenueLineItemsList.ListView
    Then I verify fields for *RLI_1 in #RevenueLineItemsList.ListView
      | fieldName   | value        |
      | name        | RLI_1 edited |
      | date_closed | 12/12/2018   |

  @list-delete
  Scenario: Revenue Line Items > List View > Delete
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity   |
      | RLI_1 | 2020-10-19T19:20:22+00:00 | <likely>    | 300       | Prospecting | <quantity> |
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    Given I open about view and login
    When I go to "RevenueLineItems" url
    When I click on Delete button for *RLI_1 in #RevenueLineItemsList.ListView
    When I Cancel confirmation alert
    Then I should see #RevenueLineItemsList view
    Then I should see *RLI_1 in #RevenueLineItemsList.ListView
    When I click on Delete button for *RLI_1 in #RevenueLineItemsList.ListView
    When I Confirm confirmation alert
    Then I should see #RevenueLineItemsList view
    Then I should not see *RLI_1 in #RevenueLineItemsList.ListView

  @delete
  Scenario: Revenue Line Items > Record View > Delete > Cancel/Confirm
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 300         | 300       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I open actions menu in #RLI_1Record
    * I choose Delete from actions menu in #RLI_1Record
     # Cancel record deletion
    When I Cancel confirmation alert
    Then I should see #RLI_1Record view
    Then I verify fields on #RLI_1Record.HeaderView
      | fieldName | value |
      | name      | RLI_1 |
    When I open actions menu in #RLI_1Record
    * I choose Delete from actions menu in #RLI_1Record
     # Confirm record deletion
    When I Confirm confirmation alert
    Then I should see #RevenueLineItemsList.ListView view
    Then I should not see *RLI_1 in #RevenueLineItemsList.ListView

  @copy
  Scenario: Revenue Line Items > Record view > Copy > Cancel
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 300         | 300       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I open actions menu in #RLI_1Record
    When I choose Copy from actions menu in #RLI_1Record
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | name     |
      | RLI_1234 |
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | likely_case | sales_stage   |
      | 400.00      | Qualification |
    When I click Cancel button on #RevenueLineItemsDrawer header
    Then I verify fields on #RLI_1Record.HeaderView
      | fieldName | value |
      | name      | RLI_1 |
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName   | value       |
      | sales_stage | Prospecting |
      | date_closed | 10/19/2018  |
      | likely_case | $300.00     |

  @copy
  Scenario: Revenue Line Items > Record view > Copy > Save
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 300         | 300       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I open actions menu in #RLI_1Record
    When I choose Copy from actions menu in #RLI_1Record
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | name     |
      | RLI_1234 |
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | likely_case | sales_stage   | date_closed |
      | 400.00      | Qualification | 11/01/2018  |
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert
    Then I verify fields on #RLI_1Record.HeaderView
      | fieldName | value    |
      | name      | RLI_1234 |
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName   | value         |
      | sales_stage | Qualification |
      | probability | 20            |
      | date_closed | 11/01/2018    |
      | likely_case | $400.00       |

  @edit-cancel
  Scenario: Revenue Line Items > Record View > Edit > Cancel
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 300         | 300       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I open actions menu in #RLI_1Record
    When I click Edit button on #RLI_1Record header
    Then I should see #RLI_1Record view
    When I provide input for #RLI_1Record.HeaderView view
      | name   |
      | RLI_14 |
    When I provide input for #RLI_1Record.RecordView view
      | likely_case | sales_stage   |
      | 400.00      | Qualification |
    When I click Cancel button on #RLI_1Record header
    Then I verify fields on #RLI_1Record.HeaderView
      | fieldName | value |
      | name      | RLI_1 |
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName   | value      |
      | likely_case | $300.00    |
      | date_closed | 10/19/2018 |

  @edit-save
  Scenario Outline: Revenue Line Items > Record View > Edit > Save
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 300         | 300       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I open actions menu in #RLI_1Record
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.HeaderView view
      | name  |
      | RLI_2 |
    When I provide input for #RLI_1Record.RecordView view
      | sales_stage   | date_closed   | likely_case   |
      | Qualification | <date_closed> | <likely_case> |
    When I click show more button on #RLI_1Record view
    When I provide input for #RLI_1Record.RecordView view
      | best_case   | worst_case   |
      | <best_case> | <worst_case> |
    When I click Save button on #RLI_1Record header
    Then I verify fields on #RLI_1Record.HeaderView
      | fieldName | value |
      | name      | RLI_2 |
    When I click show more button on #RLI_1Record view
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName   | value                     |
      | likely_case | <dollarsign><likely_case> |
      | date_closed | <date_closed>             |
      | probability | <probability>             |
      | best_case   | <dollarsign><best_case>   |
      | worst_case  | <dollarsign><worst_case>  |

    Examples:
      | likely_case | date_closed | probability | best_case | worst_case | dollarsign |
      | 455.50      | 11/20/2018  | 20          | 600.00    | 300.00     | $          |
      | -1,000.00   | 11/20/2018  | 20          | -500.00   | -250.00    | $          |

  @create_cancel_save
  Scenario Outline: Revenue Line Items > Create > Cancel/Save
    Given Opportunities records exist:
      | name  |
      | Opp_1 |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I click Create button on #RevenueLineItemsList header
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | *        | name  |
      | RecordID | RLI_1 |
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | *        | date_closed | likely_case   | opportunity_name | sales_stage    | quantity |
      | RecordID | 11/20/2018  | <likely_case> | Opp_1            | Needs Analysis | 5        |
    # Cancel RLI record creation
    When I click Cancel button on #RevenueLineItemsDrawer header
    When I click Create button on #RevenueLineItemsList header
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | *        | name  |
      | RecordID | RLI_1 |
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | *        | date_closed | likely_case   | opportunity_name | sales_stage    | quantity |
      | RecordID | 11/20/2018  | <likely_case> | Opp_1            | Needs Analysis | 5        |
    # Save RLI record
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert
    Then I verify fields for *RecordID in #RevenueLineItemsList.ListView
      | fieldName | value |
      | name      | RLI_1 |
    When I click on preview button on *RecordID in #RevenueLineItemsList.ListView
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName        | value                         |
      | date_closed      | <date_closed>                 |
      | likely_case      | <dollar_sign><likely_case>    |
      | opportunity_name | <opportunity_name>            |
      | account_name     | <account_name>                |
      | sales_stage      | <sales_stage>                 |
      | probability      | <probability>                 |
      | quantity         | <quantity>                    |
      | discount_price   | <dollar_sign><discount_price> |
      | total_amount     | <dollar_sign><total_amount>   |

    Examples:
      | date_closed | likely_case | opportunity_name | account_name | sales_stage    | probability | quantity | discount_price | total_amount | dollar_sign |
      | 11/20/2018  | 1,000.00    | Opp_1            | Acc_1        | Needs Analysis | 25          | 5.00     | 1,000.00       | 5,000.00     | $           |
      | 11/20/2018  | -1,000.00   | Opp_1            | Acc_1        | Needs Analysis | 25          | 5.00     | -1,000.00      | -5,000.00    | $           |


  @rli_with_negative_quantity @SS-367 @pr
  Scenario Outline: Revenue Line Items > Create RLI with negative quantity and convert to quote
    Given Opportunities records exist:
      | name  |
      | Opp_1 |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I click Create button on #RevenueLineItemsList header
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | *name |
      | RLI_1 |
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | *     | date_closed | likely_case   | opportunity_name | sales_stage    | quantity   | discount_amount | discount_select |
      | RLI_1 | 11/20/2018  | <likely_case> | Opp_1            | Needs Analysis | <quantity> | 10.00           | % Percent       |

    # Save RLI record
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert
    Then I verify fields for *RLI_1 in #RevenueLineItemsList.ListView
      | fieldName | value |
      | name      | RLI_1 |
    When I click on preview button on *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Preview view
    Then I verify fields on #RLI_1Preview.PreviewView
      | fieldName      | value                         |
      | date_closed    | <date_closed>                 |
      | quantity       | <quantity>                    |
      | discount_price | <dollar_sign><discount_price> |
      | total_amount   | <dollar_sign><total_amount>   |

    # Convert RLI to quote
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    When I open actions menu in #RLI_1Record
    When I choose GenerateQuote from actions menu in #RLI_1Record
    #Provide input for the following fields and Save
    When I provide input for #QuotesRecord.RecordView view
      | *   | date_quote_expected_closed |
      | Q_1 | 12/12/2020                 |
    When I click Save button on #QuotesRecord header
    When I close alert

    # Verify quantity of generated QLI record
    When I filter for the Products record *QLI_1 named "RLI_1"
    When I click on preview button on *QLI_1 in #ProductsList.ListView
    Then I should see #QLI_1Preview view
    Then I verify fields on #QLI_1Preview.PreviewView
      | fieldName | value |
      | quantity  | <quantity> |

    Examples:
      | date_closed | likely_case | quantity | discount_price | total_amount | dollar_sign |
      | 11/20/2018  | 1,000.00    | -5.00    | 1,000.00       | -4,500.00    | $           |
