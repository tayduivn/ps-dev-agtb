# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @Purchases @PLIs @job7 @pr @ent-only
Feature: Purchases and PLIs modules verification

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


  @purchase @SS-647
  Scenario: Purchased Line Items  >  Verify End Date on manually created goods PLIs
    Given ProductTemplates records exist:
      | *name  | discount_price | cost_price | list_price |
      | Prod_1 | 100            | 200        | 300        |

    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |

    # Case 1 - Product (Good - not service) is used
    # Create Purchase
    When I choose Purchases in modules menu and select "Create Purchase" menu item
    When I click show more button on #PurchasesDrawer view
    # Populate Header data
    When I provide input for #PurchasesDrawer.HeaderView view
      | *     | name       |
      | Pur_1 | Purchase 1 |
    # Populate record data
    When I provide input for #PurchasesDrawer.RecordView view
      | *     | account_name | description                  | product_template_name |
      | Pur_1 | Account One  | You've made a great purchase | Prod_1                |
    # Save
    When I click Save button on #PurchasesDrawer header
    When I close alert

    # Click Create Purchased Line Items in Mega menu
    When I choose PurchasedLineItems in modules menu and select "Create Purchased Line Item" menu item
    When I click show more button on #PurchasedLineItemsDrawer view
    # Populate Header data
    When I provide input for #PurchasedLineItemsDrawer.HeaderView view
      | *     | name       |
      | PLI_1 | Chelsea FC |
    # Populate record data
    When I provide input for #PurchasedLineItemsDrawer.RecordView view
      | *     | purchase_name | revenue | quantity | discount_amount |
      | PLI_1 | Purchase 1    | 2000    | 3        | 100             |

    # Save
    When I click Save button on #PurchasesDrawer header
    When I close alert

    # Verify start and end date in preview
    Then PurchasedLineItems *PLI_1 should have the following values in the preview:
      | fieldName          | value      |
      | name               | Chelsea FC |
      | service_start_date | now        |
      | service_end_date   | now        |

    # Verify start and end date in record view
    When I select *PLI_1 in #PurchasedLineItemsList.ListView
    When I click show more button on #PLI_1Record view
    Then I verify fields on #PLI_1Record.RecordView
      | fieldName          | value |
      | service_start_date | now   |
      | service_end_date   | now   |

    # Case 2 - No product is used
    # Create Purchase
    When I choose Purchases in modules menu and select "Create Purchase" menu item
    When I click show more button on #PurchasesDrawer view
    # Populate Header data
    When I provide input for #PurchasesDrawer.HeaderView view
      | *     | name       |
      | Pur_2 | Purchase 2 |
    # Populate record data
    When I provide input for #PurchasesDrawer.RecordView view
      | *     | account_name | description                  |
      | Pur_2 | Account One  | You've made a great purchase |
    # Save
    When I click Save button on #PurchasesDrawer header
    When I close alert

    # Click Create Purchased Line Items in Mega menu
    When I choose PurchasedLineItems in modules menu and select "Create Purchased Line Item" menu item
    When I click show more button on #PurchasedLineItemsDrawer view
    # Populate Header data
    When I provide input for #PurchasedLineItemsDrawer.HeaderView view
      | *     | name       |
      | PLI_2 | Arsenal FC |
    # Populate record data
    When I provide input for #PurchasedLineItemsDrawer.RecordView view
      | *     | purchase_name | revenue | quantity | discount_amount |
      | PLI_2 | Purchase 1    | 2000    | 3        | 100             |

    # Save
    When I click Save button on #PurchasesDrawer header
    When I close alert

    # Verify start and end date in preview
    Then PurchasedLineItems *PLI_2 should have the following values in the preview:
      | fieldName          | value      |
      | name               | Arsenal FC |
      | service_start_date | now        |
      | service_end_date   | now        |

    # Verify start and end date in record view
    When I select *PLI_2 in #PurchasedLineItemsList.ListView
    When I click show more button on #PLI_2Record view
    Then I verify fields on #PLI_2Record.RecordView
      | fieldName          | value |
      | service_start_date | now   |
      | service_end_date   | now   |


  @SS-431
  Scenario: Purchases > Calculate Purchase's Start Date and End Date fields
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |
    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | service | description            |
      | Pur_1 | Purchase 1 | true    | This is great purchase |
    And PurchasedLineItems records exist:
      | *name | revenue | date_closed | quantity | service_start_date | renewable | discount_price |
      | PLI_1 | 2000    | 2020-06-01  | 3.00     | 2020-06-01         | true      | 2000           |
      | PLI_2 | 2000    | 2020-06-01  | 3.00     | 2020-05-30         | true      | 2000           |
    # Update Purchased Line Items record
    When I choose PurchasedLineItems in modules menu
    When I select *PLI_1 in #PurchasedLineItemsList.ListView
    When I click Edit button on #PLI_1Record header
    When I click show more button on #PLI_1Record view
    When I provide input for #PLI_1Record.RecordView view
      | purchase_name | service_start_date | service_duration_value | service_duration_unit |
      | Purchase 1    | 06/01/2020         | 1                      | Year(s)               |
    When I click Save button on #PurchasedLineItemsRecord header
    When I close alert
    # Update Purchased Line Items record
    When I choose PurchasedLineItems in modules menu
    When I select *PLI_2 in #PurchasedLineItemsList.ListView
    When I click Edit button on #PLI_2Record header
    When I click show more button on #PLI_1Record view
    When I provide input for #PLI_2Record.RecordView view
      | purchase_name | service_start_date | service_duration_value | service_duration_unit |
      | Purchase 1    | 05/29/2020         | 6                      | Month(s)              |
    When I click Save button on #PurchasedLineItemsRecord header
    When I close alert
    # Verify purchase start date and end date
    Then Purchases *Pur_1 should have the following values in the preview:
      | fieldName  | value      |
      | name       | Purchase 1 |
      | start_date | 05/29/2020 |
      | end_date   | 05/31/2021 |
    # Update PLI record
    When I choose PurchasedLineItems in modules menu
    When I select *PLI_1 in #PurchasedLineItemsList.ListView
    When I click Edit button on #PLI_1Record header
    When I click show more button on #PLI_1Record view
    When I provide input for #PLI_1Record.RecordView view
      | service_start_date |
      | 05/15/2020         |
    When I click Save button on #PLI_1Record header
    When I close alert
    # Update PLI record
    When I choose PurchasedLineItems in modules menu
    When I select *PLI_2 in #PurchasedLineItemsList.ListView
    When I click Edit button on #PLI_2Record header
    When I click show more button on #PLI_2Record view
    When I provide input for #PLI_2Record.RecordView view
      | service_start_date | service_duration_value | service_duration_unit |
      | 06/29/2020         | 1                      | Year(s)               |
    When I click Save button on #PLI_1Record header
    When I close alert
    # Verify purchase start date and end date
    Then Purchases *Pur_1 should have the following values in the preview:
      | fieldName  | value      |
      | name       | Purchase 1 |
      | start_date | 05/15/2020 |
      | end_date   | 06/28/2021 |


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


  @SS-636
  Scenario: Purchased Line Items > Active Subscriptions dashlet doesn't show purchases not currently ongoing
    Given Accounts records exist:
      | *   | name        | assigned_user_id |
      | A_1 | Account One | 1                |

    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | service | renewable | description            |
      | Pur_1 | Purchase 1 | true    | true      | This is great purchase |

    And PurchasedLineItems records exist related via purchasedlineitems link to *Pur_1:
      | *     | name  | revenue | date_closed | quantity | service_start_date | service_duration_value | service_duration_unit | service | renewable | discount_price |
      | PLI_1 | PLI_1 | 1000    | 2020-06-01  | 1.00     | now -2M            | 1                      | month                 | true    | true      | 2000           |
      | PLI_2 | PLI_2 | 2000    | 2020-06-01  | 1.00     | now +1M            | 1                      | month                 | true    | true      | 2000           |
      | PLI_3 | PLI_3 | 2000    | 2020-06-01  | 1.00     | now -2M            | 1                      | month                 | true    | true      | 2000           |

    # Navigate to Renewal Console
    When I choose Home in modules menu and select "Renewals Console" menu item
    # Select Accounts tab
    When I select Accounts tab in #RenewalsConsoleView
    # Click the record to open side panel
    When I select *A_1 in #AccountsList.MultilineListView

    # Verify that dashlet is empty (nothing to display)
    Then I verify 'No active subscriptions' message appears in #RenewalsConsoleView.ActiveSubscriptionsDashlet

    # Update one PLI record so it spans today
    When I choose PurchasedLineItems in modules menu
    Then I should see *PLI_3 in #PurchasedLineItemsList.ListView
    When I select *PLI_3 in #PurchasedLineItemsList.ListView
    When I click show more button on #PLI_3Record view
    When I click Edit button on #PLI_1Record header
    When I provide input for #PLI_3Record.RecordView view
      | service_duration_value |
      | 3                      |
    When I click Save button on #PLI_3Record header
    When I close alert

    # Navigate to Renewal Console
    When I choose Home in modules menu
    # Select Accounts tab
    When I select Accounts tab in #RenewalsConsoleView
    # Click the record to open side panel
    When I select *A_1 in #AccountsList.MultilineListView

    # Verify record appears in Active Subscriptions dashlet
    Then I should see [*Pur_1] on #RenewalsConsoleView.ActiveSubscriptionsDashlet.ListView dashlet

    Then I verify *Pur_1 record info in #RenewalsConsoleView.ActiveSubscriptionsDashlet.ListView
      | fieldName | value        |
      | name      | Purchase 1   |
      | quantity  | , quantity 1 |
      | date      | in 2 months  |
      | total     | $2,000.00    |

    When I click on *Pur_1 record in #RenewalsConsoleView.ActiveSubscriptionsDashlet.ListView
    Then I should see #Pur_1Record view


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
