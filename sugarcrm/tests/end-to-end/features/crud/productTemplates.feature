# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules @ci-excluded
Feature: ProductTemplates module verification

  Background:
    Given I use default account
    Given I launch App

  @list
  Scenario: Products > List View > Preview
    Given ProductTemplates records exist:
      | *name  | discount_price | cost_price | list_price |
      | Prod#1 | 100            | 200        | 300        |
    Given I open about view and login
    When I go to "ProductTemplates" url
    Then I should see *Prod#1 in #ProductTemplatesList.ListView
    Then I verify fields for *Prod#1 in #ProductTemplatesList.ListView
      | fieldName | value  |
      | name      | Prod#1 |
    When I click on preview button on *Prod#1 in #ProductTemplatesList.ListView
    Then I should see #Prod#1Preview view
    Then I verify fields on #Prod#1Preview.PreviewView
      | fieldName      | value   |
      | name           | Prod#1   |
      | discount_price | $100.00 |
      | list_price     | $300.00 |
      | cost_price     | $200.00 |

  @list-search
  Scenario: Product Templates > List View > Filter > Search main input
    Given ProductTemplates records exist:
      | *name      | discount_price | cost_price | list_price |
      | Products_1 | 5              | 5          | 5          |
      | Products_2 | 6              | 6          | 6          |
      | Products_3 | 7              | 7          | 7          |
    Given I open about view and login
    When I go to "ProductTemplates" url
    # Search for "ProductTemplates": 3 records found
    When I search for "Products" in #ProductsList.FilterView view
    Then I should see *Products_1 in #ProductTemplatesList.ListView
    Then I should see *Products_2 in #ProductTemplatesList.ListView
    Then I should see *Products_3 in #ProductTemplatesList.ListView
    # Search for "Products_2" 1 record found
    When I search for "Products_2" in #ProductTemplatesList.FilterView view
    Then I should not see *Products_1 in #ProductTemplatesList.ListView
    Then I should see *Products_2 in #ProductTemplatesList.ListView
    Then I should not see *Products_3 in #ProductTemplatesList.ListView
    Then I verify fields for *Products_2 in #ProductTemplatesList.ListView
      | fieldName      | value      |
      | name           | Products_2 |
      | discount_price | $6.00      |
      | cost_price     | $6.00      |
      | list_price     | $6.00      |

  @list-edit
  Scenario: ProductTemplates > List View > Inline Edit
    Given ProductTemplates records exist:
      | *name | discount_price | cost_price | list_price |
      | Alex2 | 100            | 100        | 100        |
    Given I open about view and login
    When I go to "ProductTemplates" url
    Then I should see *Alex2 in #ProductTemplatesList.ListView
    When I click on Edit button for *Alex2 in #ProductTemplatesList.ListView
    When I set values for *Alex2 in #ProductTemplatesList.ListView
      | fieldName      | value        |
      | name           | Alex3 edited |
      | discount_price | 200          |
    When I click on Cancel button for *Alex2 in #ProductTemplatesList.ListView
    Then I verify fields for *Alex2 in #ProductTemplatesList.ListView
      | fieldName | value |
      | name      | Alex2 |
    When I click on Edit button for *Alex2 in #ProductTemplatesList.ListView
    When I set values for *Alex2 in #ProductTemplatesList.ListView
      | fieldName      | value        |
      | name           | Alex2 edited |
      | discount_price | 200          |
    When I click on Save button for *Alex2 in #ProductTemplatesList.ListView
    Then I verify fields for *Alex2 in #ProductTemplatesList.ListView
      | fieldName      | value        |
      | name           | Alex2 edited |
      | discount_price | $200.00      |

  @list-delete
  Scenario: Product Templates > List View > Delete
    Given ProductTemplates records exist:
      | *name | discount_price | cost_price | list_price |
      | Alex3 | 100            | 100        | 100        |
    Given I open about view and login
    When I go to "ProductTemplates" url
    When I click on Delete button for *Alex3 in #ProductTemplatesList.ListView
    When I Cancel confirmation alert
    Then I should see #ProductTemplatesList view
    Then I should see *Alex3 in #ProductTemplatesList.ListView
    When I click on Delete button for *Alex3 in #ProductTemplatesList.ListView
    When I Confirm confirmation alert
    Then I should see #ProductTemplatesList view
    Then I should not see *Alex3 in #ProductTemplatesList.ListView

  @delete
  Scenario: Product Templates > Record View > Delete
    Given  ProductTemplates records exist:
      | *name | discount_price | cost_price | list_price |
      | Alex4 | 100            | 100        | 100        |
    Given I open about view and login
    When I go to "ProductTemplates" url
    When I select *Alex4 in #ProductTemplatesList.ListView
    Then I should see #Alex4Record view
    When I open actions menu in #Alex4Record
    * I choose Delete from actions menu in #Alex4Record
    When I Confirm confirmation alert
    Then I should see #ProductTemplatesList.ListView view
    Then I should not see *Alex4 in #ProductTemplatesList.ListView

  @copy
  Scenario: ProductTemplates > Copy > Copy record from Record View
    Given  ProductTemplates records exist:
      | *name | discount_price | cost_price | list_price |
      | Alex1 | 100            | 100        | 100        |
    Given I open about view and login
    When I go to "ProductTemplates" url
    When I select *Alex1 in #ProductTemplatesList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ProductTemplatesDrawer.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #ProductTemplatesDrawer.RecordView view
      | cost_price | list_price | discount_price |
      | 5          | 5          | 5              |
    When I click Cancel button on #ProductTemplatesDrawer header
    Then I verify fields on #ProductTemplatesRecord.HeaderView
      | fieldName | value |
      | name      | Alex1 |
    Then I verify fields on #ProductTemplatesRecord.RecordView
      | fieldName  | value   |
      | cost_price | $100.00 |
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ProductTemplatesDrawer.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #ProductTemplatesDrawer.RecordView view
      | cost_price | list_price | discount_price |
      | 5          | 5          | 5              |
    When I click Save button on #ProductTemplatesDrawer header
    Then I verify fields on #ProductTemplatesRecord.HeaderView
      | fieldName | value |
      | name      | Alex2 |
    Then I verify fields on #ProductTemplatesRecord.RecordView
      | fieldName      | value |
      | cost_price     | $5.00 |
      | list_price     | $5.00 |
      | discount_price | $5.00 |

  @create
  Scenario: Product Templates > Create record record from scratch
    Given  ProductTypes records exist:
      | name      | description | list_order |
      | T y p e 1 | new Type    | 1          |
    Given  ProductCategories records exist:
      | name              | description  | list_order |
      | C a t e g o r y 1 | new Category | 1          |
    Given I open about view and login
    When I go to "ProductTemplates" url
    # Cancel new QLI creation
    When I click Create button on #ProductTemplatesList header
    When I provide input for #ProductTemplatesDrawer.HeaderView view
      | *        | name    |
      | RecordID | Alex123 |
    When I provide input for #ProductTemplatesDrawer.RecordView view
      | *        | cost_price | list_price | discount_price |
      | RecordID | 5          | 5          | 5              |
    When I click Cancel button on #ProductTemplatesDrawer header
    # Fill out and save new Product Template record
    When I click Create button on #ProductTemplatesList header
    When I provide input for #ProductTemplatesDrawer.HeaderView view
      | *        | name    |
      | RecordID | Alex123 |
    When I click show more button on #ProductTemplatesDrawer view
    When I provide input for #ProductTemplatesDrawer.RecordView view
      | *        |  cost_price | list_price | discount_price | status   | mft_part_num | description          | tax_class   | support_description |
      | RecordID |  5          | 5          | 5              | In Stock | Part#123.b   | New Product Template | Non-Taxable | Full Support        |
    When I provide input for #ProductTemplatesDrawer.RecordView view
      | *        | qty_in_stock | date_available | date_cost_price | type_name | category_name     | support_name  | support_contact | support_term | website     | weight |
      | RecordID | 50           | 10/12/2020     | 11/12/2020      | T y p e 1 | C a t e g o r y 1 | Alex Nisevich | Ruslan Golovach | One year     | www.abc.com | 150    |
    When I click show less button on #ProductTemplatesDrawer view
    When I click Save button on #ProductTemplatesDrawer header
    When I close alert
    Then I should see *RecordID in #ProductTemplatesList.ListView
    When I click on preview button on *RecordID in #ProductTemplatesList.ListView
    Then I should see #RecordIDPreview view
    When I click show more button on #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName           | value                |
      | name                | Alex123              |
      | cost_price          | $5.00                |
      | list_price          | $5.00                |
      | discount_price      | $5.00                |
      | status              | In Stock             |
      | mft_part_num        | Part#123.b           |
      | tax_class           | Non-Taxable          |
      | description         | New Product Template |
      | qty_in_stock        | 50                   |
      | date_cost_price     | 11/12/2020           |
      | type_name           | T y p e 1            |
      | category_name       | C a t e g o r y 1    |
      | support_name        | Alex Nisevich        |
      | support_contact     | Ruslan Golovach      |
      | support_description | Full Support         |
      | website             | http://www.abc.com   |
      | weight              | 150.00               |


  @pricing_frmula
  Scenario Outline: Product Templates > Create > Default Pricing Formula
    Given  Contacts records exist:
      | first_name | last_name |
      | Alex       | Nisevich  |
    Given I open about view and login
    When I go to "ProductTemplates" url
    # Fill out and save new QLI record
    When I click Create button on #ProductTemplatesList header
    When I provide input for #ProductTemplatesDrawer.HeaderView view
      | *        | name    |
      | RecordID | Alex123 |
    When I click show more button on #ProductTemplatesDrawer view
    When I provide input for #ProductTemplatesDrawer.RecordView view
      | *        | cost_price | list_price | pricing_formula   |
      | RecordID | 100        | 200        | <pricing_formula> |
    When I click show less button on #ProductTemplatesDrawer view
    When I click Save button on #ProductTemplatesDrawer header
    When I close alert
    Then I should see *RecordID in #ProductTemplatesList.ListView
    When I click on preview button on *RecordID in #ProductTemplatesList.ListView
    Then I should see #RecordIDPreview view
    When I click show more button on #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName      | value        |
      | name           | Alex123      |
      | cost_price     | $100.00      |
      | list_price     | $200.00      |
      | discount_price | <unit_price> |
    Examples:
      | pricing_formula       | unit_price |
      | Same as List          | $200.00    |
      | Profit Margin :21     | $126.58    |
      | Discount from List:5  | $190.00    |
      | Markup over Cost :6.6 | $106.60    |