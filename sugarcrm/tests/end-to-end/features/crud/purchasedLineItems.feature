# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_purchased_line_items @job7 @SS-381 @SS-424
Feature: Purchased Line Items module verification

  Background:
    Given I am logged in

  @user_profile
  Scenario: User Profile > Change license type
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Sell" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value      |
      | Sugar Sell |
    When I click on Cancel button on #UserProfile


  @list
  Scenario: Purchased Line Items  > List View > Preview
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |

    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | start_date | end_date   | service | renewable | description            |
      | Pur_1 | Purchase 1 | 2020-06-01 | 2021-05-31 | true    | true      | This is great purchase |

    And PurchasedLineItems records exist related via purchasedlineitems link to *Pur_1:
      | *     | name  | revenue | date_closed | quantity | service_start_date | service_end_date | service | renewable | description            | discount_price |
      | PLI_1 | PLI_1 | 2000    | 2020-06-01  | 3.00     | 2020-06-01         | 2021-05-31       | true    | true      | This is great purchase | 2000           |

    Then PurchasedLineItems *PLI_1 should have the following values in the preview:
      | fieldName          | value                  |
      | name               | PLI_1                  |
      | purchase_name      | Purchase 1             |
      | date_closed        | 06/01/2020             |
      | revenue            | $2,000.00              |
      | quantity           | 3.00                   |
      | total_amount       | $6,000.00              |
      | discount_price     | $2,000.00              |
      | service_start_date | 06/01/2020             |
      | service_end_date   | 05/31/2021             |
      | service            | true                   |
      | renewable          | true                   |
      | description        | This is great purchase |

  @list-search
  Scenario Outline: Purchased Line Items > List View > Filter > Search main input
    Given 3 PurchasedLineItems records exist:
      | *             | name          | revenue | service_start_date | service_end_date   | service | renewable |
      | PLI_{{index}} | PLI {{index}} | 2000    | 2020-06-0{{index}} | 2021-06-0{{index}} | true    | true      |
    # Search for specific record
    When I choose PurchasedLineItems in modules menu
    And I search for "PLI <searchIndex>" in #PurchasedLineItemsList.FilterView view
    # Verification if filtering is successful
    Then I should see [*PLI_<searchIndex>] on PurchasedLineItems list view
    And I should not see [*PLI_1, *PLI_3] on PurchasedLineItems list view
    Examples:
      | searchIndex |
      | 2           |

  @list-edit
  Scenario Outline: Purchased Line Items > List View > Inline Edit > Cancel/Save
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |

    And 2 Purchases records exist related via purchases link to *A_1:
      | *             | name               | start_date | end_date   | service | renewable | description            |
      | Pur_{{index}} | Purchase {{index}} | 2020-06-01 | 2021-05-31 | true    | true      | This is great purchase |

    And PurchasedLineItems records exist related via purchasedlineitems link to *Pur_1:
      | *     | name  | revenue | date_closed | quantity | service_start_date | service_end_date | service | renewable |
      | PLI_1 | PLI_1 | 1000    | 2020-06-01  | 1.00     | 2020-06-01         | 2021-05-31       | true    | true      |

    # Edit (or cancel editing of) record in the list view
    When I <action> *PLI_1 record in PurchasedLineItems list view with the following values:
      | fieldName     | value                  |
      | name          | PLI_<changeIndex>      |
      | purchase_name | Purchase <changeIndex> |
      | date_closed   | 06/0<changeIndex>/2020 |
      | revenue       | <changeIndex>000       |

    # Verify if edit (or cancel) is successful
    Then PurchasedLineItems *PLI_1 should have the following values in the list view:
      | fieldName     | value                    |
      | name          | PLI_<expectedIndex>      |
      | purchase_name | Purchase <expectedIndex> |
      | date_closed   | 06/0<expectedIndex>/2020 |
      | revenue       | $<expectedIndex>,000.00  |

    Examples:
      | action            | changeIndex | expectedIndex |
      | edit              | 2           | 2             |
      | cancel editing of | 2           | 1             |

  @list-delete
  Scenario Outline: Purchased Line Items  > List View > Delete > OK/Cancel
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |
    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | start_date | end_date   | service | renewable | description            |
      | Pur_1 | Purchase 1 | 2020-06-01 | 2021-05-31 | true    | true      | This is great purchase |
    And PurchasedLineItems records exist related via purchasedlineitems link to *Pur_1:
      | *     | name  | revenue | date_closed | quantity | service_start_date | service_end_date | service | renewable | discount_price |
      | PLI_1 | PLI_1 | 2000    | 2020-06-01  | 3.00     | 2020-06-01         | 2021-05-31       | true    | true      | 2000           |

    # Delete (or Cancel deletion of) record from list view
    When I <action> *PLI_1 record in PurchasedLineItems list view
    # Verify that record is (is not) deleted
    Then I <expected> see [*PLI_1] on PurchasedLineItems list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |

  @delete
  Scenario Outline: Purchased Line Items > Record View > Delete
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |
    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | start_date | end_date   | service | renewable | description            |
      | Pur_1 | Purchase 1 | 2020-06-01 | 2021-05-31 | true    | true      | This is great purchase |
    And PurchasedLineItems records exist related via purchasedlineitems link to *Pur_1:
      | *     | name  | revenue | date_closed | quantity | service_start_date | service_end_date | service | renewable | discount_price |
      | PLI_1 | PLI_1 | 2000    | 2020-06-01  | 3.00     | 2020-06-01         | 2021-05-31       | true    | true      | 2000           |

    # Delete (or Cancel deletion of) record in the record view
    When I <action> *PLI_1 record in PurchasedLineItems record view

    # Verify that record is (is not) deleted
    When I choose PurchasedLineItems in modules menu
    Then I <expected> see [*PLI_1] on PurchasedLineItems list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |

  @copy
  Scenario Outline: Purchased Line Items > Record View > Copy > Save/Cancel
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |
    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | start_date | end_date   | service | renewable | description            |
      | Pur_1 | Purchase 1 | 2020-06-01 | 2021-05-31 | true    | true      | This is great purchase |
    And PurchasedLineItems records exist related via purchasedlineitems link to *Pur_1:
      | *     | name  | revenue | date_closed | quantity | service_start_date | service_end_date | service | renewable | discount_price | description            |
      | PLI_1 | PLI_1 | 2000    | 2020-06-01  | 3.00     | 2020-06-01         | 2021-05-31       | true    | true      | 2000           | This is great purchase |

    # Copy (or cancel copy of) record in the record view
    When I <action> *PLI_1 record in PurchasedLineItems record view with the following header values:
      | *     | name              |
      | PLI_2 | PLI_<changeIndex> |

    # Verify if copy is (is not) created
    Then PurchasedLineItems *PLI_<expectedIndex> should have the following values:
      | fieldName          | value                  |
      | name               | PLI_<expectedIndex>    |
      | purchase_name      | Purchase 1             |
      | revenue            | $2,000.00              |
      | service_start_date | 06/01/2020             |
      | service            | true                   |
      | renewable          | true                   |
      | description        | This is great purchase |

    Examples:
      | action         | changeIndex | expectedIndex |
      | cancel copy of | 2           | 1             |
      | copy           | 2           | 2             |


  @create @SS-473 @SS-625
  Scenario: Purchased Line Items > Create
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |

    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | service | renewable | description            |
      | Pur_1 | Purchase 1 | true    | true      | This is great purchase |

    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity   |
      | RLI_1 | 2020-10-19T19:20:22+00:00 | <likely>    | <best>    | Prospecting | <quantity> |

    # Add EUR currency
    When I add new currency
      | iso4217 | conversion_rate |
      | EUR     | 0.9             |

    # Click Create Purchased Line Items in Mega menu
    When I choose PurchasedLineItems in modules menu and select "Create Purchased Line Item" menu item
    When I click show more button on #PurchasedLineItemsDrawer view
    # Populate Header data
    When I provide input for #PurchasedLineItemsDrawer.HeaderView view
      | *     | name       |
      | PLI_1 | Chelsea FC |
    # Populate record data
    When I provide input for #PurchasedLineItemsDrawer.RecordView view
      | *     | purchase_name | currency_id | date_closed | revenue | quantity | discount_amount | service_start_date | service_duration_value | service_duration_unit | tag     | revenuelineitem_name | commentlog            | service | renewable |
      | PLI_1 | Purchase 1    | € (EUR)     | 05/05/2020  | 2000    | 3        | 100             | 06/01/2020         | 2                      | Year(s)               | Chelsea | RLI_1                | Please buy Chelsea FC | true    | true      |

    # Save
    When I click Save button on #PurchasesDrawer header
    When I close alert

    Then PurchasedLineItems *PLI_1 should have the following values in the list view:
      | fieldName     | value               |
      | name          | Chelsea FC          |
      | purchase_name | Purchase 1          |
      | date_closed   | 05/05/2020          |
      | revenue       | €2,000.00 $2,222.22 |
      | total_amount  | €5,900.00 $6,555.56 |

    # Verify that record is created successfully
    Then PurchasedLineItems *PLI_1 should have the following values:
      | fieldName              | value               |
      | name                   | Chelsea FC          |
      | purchase_name          | Purchase 1          |
      | date_closed            | 05/05/2020          |
      | revenue                | €2,000.00 $2,222.22 |
      | total_amount           | €5,900.00 $6,555.56 |
      | quantity               | 3.00                |
      | discount_amount        | €100.00 $111.11     |
      | service                | true                |
      | renewable              | true                |
      | service_start_date     | 06/01/2020          |
      | service_duration_value | 2                   |
      | service_duration_unit  | Year(s)             |
      | service_end_date       | 05/31/2022          |
      | tag                    | Chelsea             |
      | revenuelineitem_name   | RLI_1               |
      | annual_revenue         | €2,950.00 $3,277.78 |

    When I click Edit button on #PLI_1Record header
    When I provide input for #PLI_1Record.RecordView view
      | discount_amount | discount_select |
      | 30              | % Percent       |
    When I click Save button on #PLI_1Record header
    When I close alert

    # Verify that calculation is correct when discount appied in percentages (SS-473)
    Then PurchasedLineItems *PLI_1 should have the following values:
      | fieldName       | value               |
      | revenue         | €2,000.00 $2,222.22 |
      | discount_amount | 30.00%              |
      | total_amount    | €4,200.00 $4,666.67 |
      | annual_revenue  | €2,100.00 $2,333.33 |



  @SS-441
  Scenario: Purchased Line Items > Calculate PLI's Annual Revenue field
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |

    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | service | renewable | description            |
      | Pur_1 | Purchase 1 | true    | true      | This is great purchase |

    # Click Create Purchased Line Items in Mega menu
    When I choose PurchasedLineItems in modules menu and select "Create Purchased Line Item" menu item
    When I click show more button on #PurchasedLineItemsDrawer view
    # Populate Header data
    When I provide input for #PurchasedLineItemsDrawer.HeaderView view
      | *     | name       |
      | PLI_1 | Chelsea FC |

    # Populate record data
    When I provide input for #PurchasedLineItemsDrawer.RecordView view
      | *     | purchase_name | date_closed | revenue | discount_price |
      | PLI_1 | Purchase 1    | 05/05/2020  | 2000    | 100            |

    # Save
    When I click Save button on #PurchasesDrawer header
    When I close alert

    # Verify that record is created successfully
    Then PurchasedLineItems *PLI_1 should have the following values:
      | fieldName      | value      |
      | name           | Chelsea FC |
      | total_amount   | $100.00    |
      | annual_revenue | $36,500.00 |

    # Calc Amount = $100, Duration = 1 year -> Yearly Revenue $100
    When I click Edit button on #PLI_1Record header
    When I provide input for #PLI_1Record.RecordView view
      | service | service_start_date | service_duration_value | service_duration_unit |
      | true    | 06/01/2020         | 1                      | Year(s)               |
    When I click Save button on #PLI_1Record header
    When I close alert

    # Verify that record is created successfully
    Then PurchasedLineItems *PLI_1 should have the following values:
      | fieldName      | value   |
      | annual_revenue | $100.00 |

    # Calc Amount = $300, Duration = 3 years -> Yearly Revenue = $100
    When I click Edit button on #PLI_1Record header
    When I provide input for #PLI_1Record.RecordView view
      | service | quantity | service_duration_value | service_duration_unit |
      | true    | 3        | 3                      | Year(s)               |
    When I click Save button on #PLI_1Record header
    When I close alert

    # Verify that record is created successfully
    Then PurchasedLineItems *PLI_1 should have the following values:
      | fieldName      | value   |
      | total_amount   | $300.00 |
      | annual_revenue | $100.00 |

    # Calc Amount = -$300, Duration = 3 years -> Yearly Revenue = -$100
    When I click Edit button on #PLI_1Record header
    When I provide input for #PLI_1Record.RecordView view
      | discount_price |
      | -100           |
    When I click Save button on #PLI_1Record header
    When I close alert

    # Verify that record is created successfully
    Then PurchasedLineItems *PLI_1 should have the following values:
      | fieldName      | value    |
      | total_amount   | $-300.00 |
      | annual_revenue | $-100.00 |

    # Calc Amount = $100, Duration = 3 months -> Yearly Revenue = $400
    When I click Edit button on #PLI_1Record header
    When I provide input for #PLI_1Record.RecordView view
      | discount_price | quantity | service_duration_unit |
      | 100            | 1        | Month(s)              |
    When I click Save button on #PLI_1Record header
    When I close alert

    # Verify that record is created successfully
    Then PurchasedLineItems *PLI_1 should have the following values:
      | fieldName      | value   |
      | total_amount   | $100.00 |
      | annual_revenue | $400.00 |

    # Calc Amount = $100, Duration = 18 months -> Yearly Revenue = $66.67
    When I click Edit button on #PLI_1Record header
    When I provide input for #PLI_1Record.RecordView view
      | discount_price | quantity | service_duration_value |
      | 100            | 1        | 18                     |
    When I click Save button on #PLI_1Record header
    When I close alert

    # Verify that record is created successfully
    Then PurchasedLineItems *PLI_1 should have the following values:
      | fieldName      | value   |
      | total_amount   | $100.00 |
      | annual_revenue | $66.67  |

    # Calc Amount = $18, Duration = 18 days -> Yearly Revenue = $365
    When I click Edit button on #PLI_1Record header
    When I provide input for #PLI_1Record.RecordView view
      | discount_price | quantity | service_duration_unit |
      | 18             | 1        | Day(s)                |
    When I click Save button on #PLI_1Record header
    When I close alert

    # Verify that record is created successfully
    Then PurchasedLineItems *PLI_1 should have the following values:
      | fieldName      | value   |
      | total_amount   | $18.00  |
      | annual_revenue | $365.00 |

    # Calc Amount = $1,000, Duration = 545 days -> Yearly Revenue = $669.72
    When I click Edit button on #PLI_1Record header
    When I provide input for #PLI_1Record.RecordView view
      | discount_price | quantity | service_duration_value |
      | 1000           | 1        | 545                    |
    When I click Save button on #PLI_1Record header
    When I close alert

    # Verify that record is created successfully
    Then PurchasedLineItems *PLI_1 should have the following values:
      | fieldName      | value     |
      | total_amount   | $1,000.00 |
      | annual_revenue | $669.72   |


  @user_profile
  Scenario: User Profile > Change license type
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Enterprise" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value            |
      | Sugar Enterprise |
    When I click on Cancel button on #UserProfile
