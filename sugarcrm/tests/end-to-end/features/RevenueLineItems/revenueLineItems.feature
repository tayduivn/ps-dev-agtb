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

  @T_17776 @T_18187
  Scenario: RLI > Verify that corresponding fields are auto populated when select product in the RLI edit mode
    # Create Product
    Given ProductTemplates records exist:
      | *name     | discount_price | list_price | cost_price |
      | Product_1 | 1000           | 2000       | 500        |
    # Could not related Product Category to Product Template via API
    Given ProductCategories records exist related via category_link link:
      | *name      |
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
    # Check Product Category link
    When I click category_name field on #RLI_1Record.RecordView view
    Then I should see #Category_1Record view
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    # Check Product Template link
    When I click product_template_name field on #RLI_1Record.RecordView view
    Then I should see #Product_1Record view

  @T_19044
  Scenario: RLI > Verify that quote could not be created from RLI record view when RLI has product category but does not have a product associated to the record
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
  Scenario Outline: RLI > Verify that Best and Worst amounts made read-only and equal to likely when closed won/lost sales stage is selected.
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
  Scenario: RLI > Verify that account field is populated when opportunity is selected in RLI edit view
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
      | *name |
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

  @T_24497
  Scenario: Quotes > ENT/ULT Verify that Copy of quoted RLI record creates not-quoted copy
    # Create RLI
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 3000        | 3000      | Prospecting | 3        |
    # Create Opportunity
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    # Create Account. (the linking part does not work)
    Given Accounts records exist related via accounts link:
      | *name | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given Contacts records exist:
      | last_name | first_name | phone_home     |
      | Nisevich  | Alex       | (798) 852-5170 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    # Manually add account info to Opportunity
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | opportunity_name |
      | Opp_1            |
    When I click Save button on #RLI_1Record header
    When I close alert
    # Generate Quote from RLI
    When I open actions menu in #RLI_1Record
    * I choose GenerateQuote from actions menu in #RLI_1Record
    Then I should see #QuotesRecord view
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    #Provide input for the following fields and Save
    When I provide input for #QuotesRecord.RecordView view
      | *        | date_quote_expected_closed |
      | RecordID | 12/12/2018                 |
    When I click Save button on #QuotesRecord header
    When I close alert

    # Copy quoted RLI record
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I open actions menu in #RLI_1Record
    When I choose Copy from actions menu in #RLI_1Record
    When I provide input for #RevenueLineItemsRecord.HeaderView view
      | *         | name  |
      | RecordID1 | RLI_2 |
    When I click Save button on #RevenueLineItemsRecord header
    When I close alert
    Then I verify fields on #RevenueLineItemsRecord.RecordView
      | fieldName  | value |
      | quote_name |       |


  @T_20161
  Scenario Outline: RLI > Verify that default Likely/Best/Worst amounts is copied from Calculated Revenue Line Item amount when create a new RLI
    # Create Opportunity linked to account
    Given Opportunities records exist:
      | name  |
      | Opp_1 |
    Given Accounts records exist related via accounts link:
      | *name |
      | Acc_1 |

    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I click Create button on #RevenueLineItemsList header
    When I click show more button on #RevenueLineItemsRecord view

    # Provide RLI name in Header View
    When I provide input for #RevenueLineItemsRecord.HeaderView view
      | *        | name |
      | RecordID | Test |
    # Fill-out some fields in the record view
    When I provide input for #RevenueLineItemsRecord.RecordView view
      | *        | date_closed | quantity      | discount_price | opportunity_name |
      | RecordID | 11/20/2018  | <myQuoantity> | <myUnitPrice>  | Opp_1            |

    # Verification before discount is set
    Then I verify fields on #RevenueLineItemsRecord.RecordView
      | fieldName    | value                    |
      | likely_case  | <expectedValue>          |
      | best_case    | <expectedValue>          |
      | worst_case   | <expectedValue>          |
      | total_amount | <expectedValueFormatted> |

    # Add some discount
    When I provide input for #RevenueLineItemsRecord.RecordView view
      | *        | discount_amount | sales_stage   |
      | RecordID | <myDiscount>    | Qualification |

    # Verification after discount is set that only Calculated RLI value is changed but likely/best/worst value stayed the same
    Then I verify fields on #RevenueLineItemsRecord.RecordView
      | fieldName    | value                  |
      | likely_case  | <expectedValue>        |
      | best_case    | <expectedValue>        |
      | worst_case   | <expectedValue>        |
      | total_amount | <totalAmountFormatted> |

    # Overwrite calculated likely value
    When I provide input for #RevenueLineItemsRecord.RecordView view
      | *        | likely_case |
      | RecordID | 100.00      |
    When I click show less button on #RevenueLineItemsRecord view
    When I click Save button on #RevenueLineItemsRecord header
    When I close alert

    # Going back to record view
    When I select *RecordID in #RevenueLineItemsList.ListView
    Then I should see #RecordIDRecord view
    When I click show more button on #RecordIDRecord view

    # Verify values on the record view
    Then I verify fields on #RecordIDRecord.RecordView
      | fieldName    | value                    |
      | total_amount | <totalAmountFormatted>   |
      | likely_case  | $100.00                  |
      | best_case    | <expectedValueFormatted> |
      | worst_case   | <expectedValueFormatted> |

    Examples:
      | myQuoantity | myUnitPrice | myDiscount | totalAmountFormatted | expectedValue | expectedValueFormatted |
      | 5           | 5           | 3.50       | $21.50               | 25.00         | $25.00                 |
      | 10          | 25          | 200        | $50.00               | 250.00        | $250.00                |
      | 1.5         | 5.50        | 3.25       | $5.00                | 8.25          | $8.25                  |
