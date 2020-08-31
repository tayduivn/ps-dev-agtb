# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@rli @job8 @ent-only @pr
Feature: RLI module verification - coterm

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


  @SS-737 @SS-678
  Scenario Outline: Set duration of coterm RLIs to make End Date remain fixed
        # Create product records
    Given ProductTemplates records exist:
      | *name  | discount_price | cost_price | list_price | service | service_duration_value | service_duration_unit | renewable |
      | Prod_1 | 100            | 200        | 300        | true    | 18                     | month                 | true      |

    Given ProductCategories records exist related via category_link link:
      | *name      |
      | Category_1 |

    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |

    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | service | renewable | description            |
      | Pur_1 | Purchase 1 | true    | true      | This is great purchase |

    Given RevenueLineItems records exist:
      | *name | date_closed | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | now         | 100        | 200         | 300       | Prospecting | 1        |

    Given Opportunities records exist related via opportunities link to *RLI_1:
      | name  |
      | Opp_1 |

    # Link Product Category to the product
    When I go to "ProductTemplates" url
    When I select *Prod_1 in #ProductTemplatesList.ListView
    When I click Edit button on #Prod_1Record header
    When I provide input for #Prod_1Record.RecordView view
      | category_name |
      | Category_1    |
    When I click Save button on #Prod_1Record header
    When I close alert

    # Link service Product to the purchase record
    When I choose Purchases in modules menu
    When I select *Pur_1 in #PurchasesList.ListView
    When I click Edit button on #Pur_1Record header
    When I click show more button on #Pur_1Record view
    When I provide input for #Pur_1Record.RecordView view
      | product_template_name |
      | Prod_1                |
    When I click Save button on #Pur_1Record header
    When I close alert

    # Create PLI record linked to the purchase record
    When I choose PurchasedLineItems in modules menu and select "Create Purchased Line Item" menu item
    When I click show more button on #PurchasedLineItemsDrawer view
    When I provide input for #PurchasedLineItemsDrawer.HeaderView view
      | *     | name  |
      | PLI_1 | PLI_1 |
    When I provide input for #PurchasedLineItemsDrawer.RecordView view
      | *     | purchase_name | service_start_date |
      | PLI_1 | Purchase 1    | 07/01/2030         |
    When I click Save button on #PurchasedLineItemsRecord header
    When I close alert

    # Mark RLI record as service
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    When I click Edit button on #RLI_1Record header
    When I click show more button on #RLI_1Record view
    When I provide input for #RLI_1Record.RecordView view
      | service |
      | true    |
    When I click Save button on #RLI_1Record header
    When I close alert

    # Verify that default duration is set to 1 year and 'Add on to' field is empty
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName              | value   |
      | service                | true    |
      | add_on_to_name         |         |
      | service_duration_value | 1       |
      | service_duration_unit  | Year(s) |
      | service_start_date     | now     |
      | likely_case            | $200.00 |
      | best_case              | $300.00 |
      | worst_case             | $100.00 |
      | renewable              | false   |
      | discount_price         | $200.00 |

    # Link PLI to RLI in the 'Add on to' field and set service_start_date
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | add_on_to_name | service_start_date |
      | PLI_1          | 07/01/2030         |
    When I click Save button on #RLI_1Record header
    When I close alert

    # Verify that service duration value and units are updated
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName              | value              |
      | add_on_to_name         | PLI_1              |
      | service_duration_value | 18                 |
      | service_duration_unit  | Month(s)           |
      | service_start_date     | 07/01/2030         |
      | service_end_date       | <service_end_date> |
      | renewable              | true               |
      | discount_price         | $100.00            |
      | total_amount           | $100.00            |
      | category_name          | Category_1         |
      | list_price             | $300.00            |
      | cost_price             | $200.00            |

    # Edit RLI - move service start date by 6 months
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | service_start_date |
      | 01/01/2031         |
    When I click Save button on #RLI_1Record header
    When I close alert

    # Verify service duration is updated properly
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName              | value              |
      | service_duration_value | 1                  |
      | service_duration_unit  | Year(s)            |
      | service_start_date     | 01/01/2031         |
      | service_end_date       | <service_end_date> |
      | total_amount           | $66.67             |

    # Edit RLI - move service start date back 15 days
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | service_start_date |
      | 12/17/2030         |
    When I click Save button on #RLI_1Record header
    When I close alert

    # Verify service duration is updated properly
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName              | value              |
      | service_duration_value | 380                |
      | service_duration_unit  | Day(s)             |
      | service_start_date     | 12/17/2030         |
      | service_end_date       | <service_end_date> |
      | total_amount           | $69.41             |

    # Edit RLI - move service start date forward 1 day
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | service_start_date |
      | 12/18/2030         |
    When I click Save button on #RLI_1Record header
    When I close alert

    # Verify service duration is updated properly
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName              | value              |
      | service_duration_value | 379                |
      | service_duration_unit  | Day(s)             |
      | service_start_date     | 12/18/2030         |
      | service_end_date       | <service_end_date> |
      | total_amount           | $69.22             |

    # Edit RLI - make service start day one day after service end day
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | service_start_date |
      | 01/01/2032         |
    When I click Save button on #RLI_1Record header

    # Verify record could not be saved and error message is displayed
    Then I check alert
      | type  | message                                            |
      | Error | Error Please resolve any errors before proceeding. |
    When I close alert

    # Edit RLI - make service start day the same as service end day
    When I provide input for #RLI_1Record.RecordView view
      | service_start_date |
      | <service_end_date> |
    When I click Save button on #RLI_1Record header
    When I close alert

    # Verify service duration is updated properly
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName              | value              |
      | service_duration_value | 1                  |
      | service_duration_unit  | Day(s)             |
      | service_start_date     | <service_end_date> |
      | service_end_date       | <service_end_date> |
      | total_amount           | $0.18              |

    Examples:
      | service_end_date |
      | 12/31/2031       |


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
