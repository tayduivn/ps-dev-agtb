# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_products @job3
Feature: Products module verification

  Background:
    Given I use default account
    Given I launch App

  @list
  Scenario: Products > List View > Preview
    Given Products records exist:
      | *name | discount_price | cost_price | list_price |
      | Alex3 | 100            | 100        | 100        |
    Given I open about view and login
    When I go to "Products" url
    Then I should see *Alex3 in #ProductsList.ListView
    Then I verify fields for *Alex3 in #ProductsList.ListView
      | fieldName | value |
      | name      | Alex3 |
    When I click on preview button on *Alex3 in #ProductsList.ListView
    Then I should see #Alex3Preview view
    Then I verify fields on #Alex3Preview.PreviewView
      | fieldName      | value   |
      | name           | Alex3   |
      | discount_price | $100.00 |
      | list_price     | $100.00 |
      | cost_price     | $100.00 |

  @list-search
  Scenario: Products > List View > Filter > Search main input
    Given Products records exist:
      | *name      | discount_price | cost_price | list_price |
      | Products_1 | 5              | 5          | 5          |
      | Products_2 | 6              | 6          | 6          |
      | Products_3 | 7              | 7          | 7          |
    Given I open about view and login
    When I go to "Products" url
    # Search for "Products": 3 records found
    When I search for "Products" in #ProductsList.FilterView view
    Then I should see *Products_1 in #ProductsList.ListView
    Then I should see *Products_2 in #ProductsList.ListView
    Then I should see *Products_3 in #ProductsList.ListView
    # Search for "Products_2" 1 record found
    When I search for "Products_2" in #ProductsList.FilterView view
    Then I should not see *Products_1 in #ProductsList.ListView
    Then I should see *Products_2 in #ProductsList.ListView
    Then I should not see *Products_3 in #ProductsList.ListView
    Then I verify fields for *Products_2 in #ProductsList.ListView
      | fieldName      | value      |
      | name           | Products_2 |
      | discount_price | $6.00      |
      | cost_price     | $6.00      |

  @list-edit
  Scenario: Products > List View > Inline Edit
    Given Products records exist:
      | *name | discount_price | cost_price | list_price |
      | Alex2 | 100            | 100        | 100        |
    Given I open about view and login
    When I go to "Products" url
    Then I should see *Alex2 in #ProductsList.ListView
    When I click on Edit button for *Alex2 in #ProductsList.ListView
    When I set values for *Alex2 in #ProductsList.ListView
      | fieldName      | value        |
      | name           | Alex3 edited |
      | discount_price | 200          |
    When I click on Cancel button for *Alex2 in #ProductsList.ListView
    Then I verify fields for *Alex2 in #ProductsList.ListView
      | fieldName | value |
      | name      | Alex2 |
    When I click on Edit button for *Alex2 in #ProductsList.ListView
    When I set values for *Alex2 in #ProductsList.ListView
      | fieldName      | value        |
      | name           | Alex2 edited |
      | discount_price | 200          |
    When I click on Save button for *Alex2 in #ProductsList.ListView
    Then I verify fields for *Alex2 in #ProductsList.ListView
      | fieldName      | value        |
      | name           | Alex2 edited |
      | discount_price | $200.00      |

  @list-delete
  Scenario: Products > List View > Delete
    Given Products records exist:
      | *name | discount_price | cost_price | list_price |
      | Alex3 | 100            | 100        | 100        |
    Given I open about view and login
    When I go to "Products" url
    When I click on Delete button for *Alex3 in #ProductsList.ListView
    When I Cancel confirmation alert
    Then I should see #ProductsList view
    Then I should see *Alex3 in #ProductsList.ListView
    When I click on Delete button for *Alex3 in #ProductsList.ListView
    When I Confirm confirmation alert
    Then I should see #ProductsList view
    Then I should not see *Alex3 in #ProductsList.ListView

  @delete
  Scenario: Products > Record View > Delete
    Given  Products records exist:
      | *name | discount_price | cost_price | list_price |
      | Alex4 | 100            | 100        | 100        |
    Given I open about view and login
    When I go to "Products" url
    When I select *Alex4 in #ProductsList.ListView
    Then I should see #Alex4Record view
    When I open actions menu in #Alex4Record
    * I choose Delete from actions menu in #Alex4Record
    When I Confirm confirmation alert
    Then I should see #ProductsList.ListView view
    Then I should not see *Alex4 in #ProductsList.ListView

  @copy
  Scenario: Products > Copy > Copy record from Record View
    Given  Products records exist:
      | *name | discount_price | cost_price | list_price |
      | Alex1 | 100            | 100        | 100        |
    Given I open about view and login
    When I go to "Products" url
    When I select *Alex1 in #ProductsList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ProductsDrawer.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #ProductsDrawer.RecordView view
      | cost_price | list_price | discount_price |
      | 5          | 5          | 5              |
    When I click Cancel button on #ProductsDrawer header
    Then I verify fields on #ProductsRecord.HeaderView
      | fieldName | value |
      | name      | Alex1 |
    Then I verify fields on #ProductsRecord.RecordView
      | fieldName  | value   |
      | cost_price | $100.00 |
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ProductsDrawer.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #ProductsDrawer.RecordView view
      | cost_price | list_price | discount_price |
      | 5          | 5          | 5              |
    When I click Save button on #ProductsDrawer header
    Then I verify fields on #ProductsRecord.HeaderView
      | fieldName | value |
      | name      | Alex2 |
    Then I verify fields on #ProductsRecord.RecordView
      | fieldName      | value |
      | cost_price     | $5.00 |
      | list_price     | $5.00 |
      | discount_price | $5.00 |

  @create
  Scenario: Products > Create QLI record from scratch
    Given  Contacts records exist:
      | first_name | last_name |
      | Alex       | Nisevich  |
    Given I open about view and login
    When I go to "Products" url
    # Cancel new QLI creation
    When I click Create button on #ProductsList header
    When I provide input for #ProductsDrawer.HeaderView view
      | *        | name    |
      | RecordID | Alex123 |
    When I provide input for #ProductsDrawer.RecordView view
      | *        | cost_price | list_price | discount_price |
      | RecordID | 5          | 5          | 5              |
    When I click Cancel button on #ProductsDrawer header
    # Fill out and save new QLI record
    When I click Create button on #ProductsList header
    When I provide input for #ProductsDrawer.HeaderView view
      | *        | name    |
      | RecordID | Alex123 |
    When I click show more button on #ProductsDrawer view
    When I provide input for #ProductsDrawer.RecordView view
      | *        | cost_price | list_price | discount_price | status | contact_name  | quantity | mft_part_num | discount_amount | description    |
      | RecordID | 5          | 5          | 5              | Quoted | Alex Nisevich | 10.00    | Part#123.b   | 7               | New QLI Record |
    When I click show less button on #ProductsDrawer view
    When I click Save button on #ProductsDrawer header
    When I close alert
    Then I should see *RecordID in #ProductsList.ListView
    When I click on preview button on *RecordID in #ProductsList.ListView
    Then I should see #RecordIDPreview view
    When I click show more button on #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName       | value          |
      | name            | Alex123        |
      | cost_price      | $5.00          |
      | list_price      | $5.00          |
      | discount_price  | $5.00          |
      | status          | Quoted         |
      | contact_name    | Alex Nisevich  |
      | quantity        | 10.00          |
      | mft_part_num    | Part#123.b     |
      | discount_amount | 7.00%          |
      | description     | New QLI Record |

  @create @ent-only
  Scenario: Products > Create QLI record from Product Template
    Given ProductTemplates records exist:
      | *name  | discount_price | cost_price | list_price | quantity | mft_part_num                 | tax_class | service | renewable | service_duration_value | service_duration_unit |
      | Prod_1 | 500            | 300        | 700        | 10       | B.H. Edwards Inc 72868XYZ987 | Taxable   | true    | true      | 18                     | month                 |

    Given I open about view and login
    When I go to "Products" url
    When I click Create button on #ProductsList header
    When I provide input for #ProductsDrawer.RecordView view
      | *        | product_template_name | discount_amount | quantity |
      | RecordID | Prod_1                | 6               | 10       |
    When I click Save button on #ProductsDrawer header
    When I close alert
    Then I should see *RecordID in #ProductsList.ListView
    When I click on preview button on *RecordID in #ProductsList.ListView
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName              | value                        |
      | name                   | Prod_1                       |
      | cost_price             | $300.00                      |
      | list_price             | $700.00                      |
      | discount_price         | $500.00                      |
      | quantity               | 10.00                        |
      | mft_part_num           | B.H. Edwards Inc 72868XYZ987 |
      | discount_amount        | 6.00%                        |
      | service                | true                         |
      | renewable              | true                         |
      | service_duration_value | 18                           |
      | service_duration_unit  | Month(s)                     |
      | service_start_date     | now                          |

  @edit
  Scenario: Products > Edit existing QLI record > Cancel/Save
    Given Products records exist:
      | *name | discount_price | cost_price | list_price | quantity | discount_amount | mft_part_num                 |
      | QLI_1 | 500            | 300        | 700        | 10       | 6               | B.H. Edwards Inc 72868XYZ987 |
    Given I open about view and login
    When I go to "Currencies" url
    When I click Create button on #CurrenciesList header
    When I provide input for #CurrenciesDrawer.RecordView view
      | iso4217 | conversion_rate |
      | EUR     | 0.5             |
    When I click Save button on #CurrenciesDrawer header
    When I close alert
    When I go to "Products" url
    Then I should see *QLI_1 in #ProductsList.ListView
    When I select *QLI_1 in #ProductsList.ListView
    Then I should see #QLI_1Record view
    When I click Edit button on #QLI_1Record header
    When I provide input for #ProductsRecord.HeaderView view
      | name     |
      | Alex 123 |
    When I provide input for #ProductsRecord.RecordView view
      | quantity |
      | 5        |
    When I click Cancel button on #ProductsRecord header
    Then I verify fields on #QLI_1Record.HeaderView
      | fieldName | value |
      | name      | QLI_1 |
    Then I verify fields on #QLI_1Record.RecordView
      | fieldName | value |
      | quantity  | 10.00 |
    When I click Edit button on #QLI_1Record header
    When I provide input for #QLI_1Record.RecordView view
      | quantity | discount_price | cost_price | list_price | currency_id |
      | 5        | 1000           | 1000       | 1000       | € (EUR)     |
    When I click Save button on #QLI_1Record header
    When I close alert
    Then I verify fields on #QLI_1Record.RecordView
      | fieldName      | value   |
      | quantity       | 5.00    |
      | discount_price | €500.00 |
      | cost_price     | €500.00 |
      | list_price     | €500.00 |

  @create @discount_type_field
  Scenario: Products > Change discount type
    Given  Contacts records exist:
      | first_name | last_name |
      | Alex       | Nisevich  |
    Given I open about view and login
    When I go to "Products" url
    When I click Create button on #ProductsList header
    When I provide input for #ProductsDrawer.HeaderView view
      | *        | name    |
      | RecordID | Alex123 |
    When I click show more button on #ProductsDrawer view
    When I provide input for #ProductsDrawer.RecordView view
      | *        | cost_price | discount_price | discount_amount | discount_select |
      | RecordID | 5          | 200            | 10.00           | $ US Dollar     |
    When I click show less button on #ProductsDrawer view
    When I click Save button on #ProductsDrawer header
    When I close alert
    Then I should see *RecordID in #ProductsList.ListView
    When I click on preview button on *RecordID in #ProductsList.ListView
    Then I should see #RecordIDPreview view
    When I click show more button on #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName       | value   |
      | name            | Alex123 |
      | discount_price  | $200.00 |
      | discount_amount | $10.00  |
    When I select *RecordID in #ProductsList.ListView
    Then I should see #RecordIDRecord view
    When I click Edit button on #RecordIDRecord header
    When I provide input for #RecordIDRecord.RecordView view
      | discount_select |
      | % Percent       |
    When I click Save button on #RecordIDRecord header
    When I close alert
    Then I verify fields on #RecordIDRecord.RecordView
      | fieldName       | value  |
      | discount_amount | 10.00% |

  @create @verify_QLI_accountname
  Scenario: Quotes > Record View > QLI Table > Add QLI > Account Verification
    Given ProductTemplates records exist:
      | *name  | discount_price | cost_price | list_price | quanity | my_favorite |
      | Prod_1 | 100            | 200        | 300        | 1       | true        |
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_1 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_1:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    And Accounts records exist:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_2 | City 2               | 10050 North Wolf       | 95014                      | CA                    | USA                     |

    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_1 in #QuotesList.ListView
    Then I should see #Quote_1Record view

    # Add 1st Line Item
    When I choose createLineItem on QLI section on #Quote_1Record view
    When I provide input for #Quote_1Record.QliTable.QliRecord view
      | *       | quantity | product_template_name | discount_price | discount_amount |
      | RecordA | 2.00     | Prod_1                | 100            | 2.00            |

    #Save and verify account name has been populated
    When I click on save button on QLI #Quote_1Record.QliTable.QliRecord record
    When I close alert
    When I click product_template_name field on #RecordAQLIRecord view
    Then I should see #ProductsRecord view
    When I click show more button on #ProductsRecord view
    Then I verify fields on #ProductsRecord.RecordView
      | fieldName          | value         |
      | assigned_user_name | Administrator |
      | account_name       | Acc_1         |

    When I choose Quotes in modules menu
    When I select *Quote_1 in #QuotesList.ListView
    Then I should see #Quote_1Record view

        # Add 2nd Line Item
    When I choose createLineItem on QLI section on #Quote_1Record view
    When I provide input for #Quote_1Record.QliTable.QliRecord view
      | *       | quantity | product_template_name | discount_price | discount_amount |
      | RecordB | 4.00     | Prod_A                | 0              | 0.00            |

    #Save
    When I click on save button on QLI #Quote_1Record.QliTable.QliRecord record
    When I close alert

    # Add New Line Item via quick pick dashlet
    When I choose Quotes in modules menu
    When I select *Quote_1 in #QuotesList.ListView
    Then I should see #Quote_1Record view

    # Select 'Favorites' tab in Product Catalog Quick Picks dashlet
    When I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Add one product from the dashlet and cancel
    When I click *Prod_1 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet
    When I click Add2Quote button on #Prod_1Drawer header

    #Save and verify account name has been populated
    When I click on save button on QLI #Quote_1Record.QliTable.QliRecord record
    When I close alert
    When I click product_template_name field on #RecordAQLIRecord view
    Then I should see #ProductsRecord view
    When I click show more button on #ProductsRecord view
    Then I verify fields on #ProductsRecord.RecordView
      | fieldName          | value         |
      | assigned_user_name | Administrator |
      | account_name       | Acc_1         |

   # Update account in quotes updates QLI records @AT-329
   # Edit quote and update the billing account to a new selection
    When I choose Quotes in modules menu
    When I select *Quote_1 in #QuotesList.ListView
    Then I should see #Quote_1Record view
    When I open actions menu in #Quote_1Record
    When I click Edit button on #Quote_1Record header
    When I toggle Billing_and_Shipping panel on #Quote_1Record.RecordView view
    When I provide input for #Quote_1Record.RecordView view
      | billing_account_name |
      | Acc_2                |
    When I Confirm confirmation alert
    When I click Save button on #Quote_1Record header
    Then I close alert

    # Verification of the update of the billing account on QLI
    When I go to "Products" url
    Then I should see *RecordA in #ProductsList.ListView
    When I select *RecordA in #ProductsList.ListView
    Then I should see #RecordARecord view
    Then I verify fields on #RecordARecord.HeaderView
      | fieldName | value  |
      | name      | Prod_1 |
    Then I verify fields on #RecordARecord.RecordView
      | fieldName    | value |
      | account_name | Acc_2 |

    When I go to "Products" url
    Then I should see *RecordB in #ProductsList.ListView
    When I select *RecordB in #ProductsList.ListView
    Then I should see #RecordBRecord view
    Then I verify fields on #RecordARecord.HeaderView
      | fieldName | value  |
      | name      | Prod_A |
    Then I verify fields on #RecordARecord.RecordView
      | fieldName    | value |
      | account_name | Acc_2 |
