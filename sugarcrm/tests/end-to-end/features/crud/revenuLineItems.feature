# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules @rli
Feature: Quotes module verification

  Background:
    Given I use default account
    Given I launch App

  @list-preview @T_34545
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

  @list-search @T_34546
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
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity   |
      | RLI_1 | 2020-10-19T19:20:22+00:00 | <likely>    | 300       | Prospecting | <quantity> |
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

  @list-delete @T_34548
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

  @delete @T_34552
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

  @copy @T_34553
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
    When I provide input for #RevenueLineItemsRecord.HeaderView view
      | name     |
      | RLI_1234 |
    When I provide input for #RevenueLineItemsRecord.RecordView view
      | likely_case | sales_stage   |
      | 400.00      | Qualification |
    When I click Cancel button on #RevenueLineItemsRecord header
    Then I verify fields on #RLI_1Record.HeaderView
      | fieldName | value |
      | name      | RLI_1 |
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName   | value       |
      | sales_stage | Prospecting |
      | date_closed | 10/19/2018  |
      | likely_case | $300.00     |

  @copy @T_34553
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
    When I provide input for #RevenueLineItemsRecord.HeaderView view
      | name     |
      | RLI_1234 |
    When I provide input for #RevenueLineItemsRecord.RecordView view
      | likely_case | sales_stage   | date_closed |
      | 400.00      | Qualification | 11/01/2018  |
    When I click Save button on #RevenueLineItemsRecord header
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

  @edit-cancel @T_34554
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

  @edit-save @T_34554
  Scenario: Revenue Line Items > Record View > Edit > Save
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
      | sales_stage   | date_closed | likely_case |
      | Qualification | 11/20/2018  | 455.50      |
    When I click show more button on #RLI_1Record view
    When I provide input for #RLI_1Record.RecordView view
      | best_case | worst_case |
      | 600       | 300        |
    When I click Save button on #RLI_1Record header
    Then I verify fields on #RLI_1Record.HeaderView
      | fieldName | value |
      | name      | RLI_2 |
    When I click show more button on #RLI_1Record view
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName   | value      |
      | likely_case | $455.50    |
      | date_closed | 11/20/2018 |
      | probability | 20         |
      | best_case   | $600.00    |
      | worst_case  | $300.00    |

  @create_cancel_save @T_34555
  Scenario: Revenue Line Items > Create > Cancel/Save
    Given Opportunities records exist:
      | name  |
      | Opp_1 |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I click Create button on #RevenueLineItemsList header
    When I provide input for #RevenueLineItemsRecord.HeaderView view
      | *        | name  |
      | RecordID | RLI_1 |
    When I provide input for #RevenueLineItemsRecord.RecordView view
      | *        | date_closed | likely_case | opportunity_name | sales_stage    | quantity |
      | RecordID | 11/20/2018  | 1000        | Opp_1            | Needs Analysis | 5        |
    # Cancel RLI record creation
    When I click Cancel button on #RevenueLineItemsRecord header
    When I click Create button on #RevenueLineItemsList header
    When I provide input for #RevenueLineItemsRecord.HeaderView view
      | *        | name  |
      | RecordID | RLI_1 |
    When I provide input for #RevenueLineItemsRecord.RecordView view
      | *        | date_closed | likely_case | opportunity_name | sales_stage    | quantity |
      | RecordID | 11/20/2018  | 1000        | Opp_1            | Needs Analysis | 5        |
    # Save RLI record
    When I click Save button on #RevenueLineItemsRecord header
    When I close alert
    Then I verify fields for *RecordID in #RevenueLineItemsList.ListView
      | fieldName | value |
      | name      | RLI_1 |
    When I click on preview button on *RecordID in #RevenueLineItemsList.ListView
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName        | value          |
      | date_closed      | 11/20/2018     |
      | likely_case      | $1,000.00      |
      | opportunity_name | Opp_1          |
      | account_name     | Acc_1          |
      | sales_stage      | Needs Analysis |
      | probability      | 25             |
      | quantity         | 5.00           |
      | discount_price   | $1,000.00      |
      | total_amount     | $5,000.00      |
