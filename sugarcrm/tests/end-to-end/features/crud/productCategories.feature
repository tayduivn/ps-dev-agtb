# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_productCategories @job5
Feature: productCategories module verification

  Background:
    Given I use default account
    Given I launch App

  @list
  Scenario: Product Categories > List View > Preview
    Given ProductCategories records exist:
      | *name | list_order | description       |
      | Alex1 | 4          | Alex New Category |
    Given I open about view and login
    When I go to "ProductCategories" url
    Then I should see *Alex1 in #ProductCategoriesList.ListView
    Then I verify fields for *Alex1 in #ProductCategoriesList.ListView
      | fieldName | value |
      | name      | Alex1 |
    When I click on preview button on *Alex1 in #ProductCategoriesList.ListView
    Then I should see #Alex1Preview view
    Then I verify fields on #Alex1Preview.PreviewView
      | fieldName   | value             |
      | name        | Alex1             |
      | description | Alex New Category |
      | list_order  | 4                 |

  @list-search
  Scenario: Product Categories > List View > Filter > Search main input
    Given ProductCategories records exist:
      | *name               | list_order | description |
      | ProductCategories_1 | 5          | 5           |
      | ProductCategories_2 | 6          | 6           |
      | ProductCategories_3 | 7          | 7           |
    Given I open about view and login
    When I go to "ProductCategories" url
    # Search for "ProductCategories": 3 records found
    When I search for "ProductCategories" in #ProductCategoriesList.FilterView view
    Then I should see *ProductCategories_1 in #ProductCategoriesList.ListView
    Then I should see *ProductCategories_2 in #ProductCategoriesList.ListView
    Then I should see *ProductCategories_3 in #ProductCategoriesList.ListView
    # Search for "ProductCategories_2" 1 record found
    When I search for "ProductCategories_2" in #ProductCategoriesList.FilterView view
    Then I should not see *ProductCategories_1 in #ProductCategoriesList.ListView
    Then I should see *ProductCategories_2 in #ProductCategoriesList.ListView
    Then I should not see *ProductCategories_3 in #ProductCategoriesList.ListView
    Then I verify fields for *ProductCategories_2 in #ProductCategoriesList.ListView
      | fieldName   | value               |
      | name        | ProductCategories_2 |
      | description | 6                   |
      | list_order  | 6                   |


  @list-edit
  Scenario: Product Categories > List View > Inline Edit
    Given ProductCategories records exist:
      | *name | list_order | description |
      | Alex1 | 4          | Prod. category  |
    Given I open about view and login
    When I go to "ProductCategories" url
    When I click on Edit button for *Alex1 in #ProductCategoriesList.ListView
    When I set values for *Alex1 in #ProductCategoriesList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
    When I click on Cancel button for *Alex1 in #ProductCategoriesList.ListView
    Then I verify fields for *Alex1 in #ProductCategoriesList.ListView
      | fieldName | value |
      | name      | Alex1 |
    When I click on Edit button for *Alex1 in #ProductCategoriesList.ListView
    When I set values for *Alex1 in #ProductCategoriesList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
    When I click on Save button for *Alex1 in #ProductCategoriesList.ListView
    Then I verify fields for *Alex1 in #ProductCategoriesList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |

  @list-delete
  Scenario: Product Categories > List View > Delete
    Given ProductCategories records exist:
      | *name | list_order | status | description |
      | Alex1 | 4          | Active | Prod. category  |
    Given I open about view and login
    When I go to "ProductCategories" url
    When I click on Delete button for *Alex1 in #ProductCategoriesList.ListView
    When I Cancel confirmation alert
    Then I should see #ProductCategoriesList view
    Then I should see *Alex1 in #ProductCategoriesList.ListView
    When I click on Delete button for *Alex1 in #ProductCategoriesList.ListView
    When I Confirm confirmation alert
    Then I should see #ProductCategoriesList view
    Then I should not see *Alex1 in #ProductCategoriesList.ListView

  @delete
  Scenario: Product Categories > Record View > Delete
    Given  ProductCategories records exist:
      | *name | list_order | description   |
      | Alex1 | 4          | Alex New category |
    Given I open about view and login
    When I go to "ProductCategories" url
    When I select *Alex1 in #ProductCategoriesList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    * I choose Delete from actions menu in #Alex1Record
    When I Confirm confirmation alert
    Then I should see #ProductCategoriesList.ListView view
    Then I should not see *Alex1 in #ProductCategoriesList.ListView

  @copy
  Scenario: Product Categories > Record View > Copy
    Given  ProductCategories records exist:
      | *name | list_order | description   |
      | Alex1 | 4          | Alex New category |
    Given I open about view and login
    When I go to "ProductCategories" url
    When I select *Alex1 in #ProductCategoriesList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ProductCategoriesDrawer.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #ProductCategoriesDrawer.RecordView view
      | list_order | description |
      | 5          | Great category  |
    When I click Cancel button on #ProductCategoriesDrawer header
    Then I verify fields on #ProductCategoriesRecord.HeaderView
      | fieldName | value |
      | name      | Alex1 |
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ProductCategoriesDrawer.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #ProductCategoriesDrawer.RecordView view
      | list_order | description |
      | 5          | Great category  |
    When I click Save button on #ProductCategoriesDrawer header
    Then I verify fields on #ProductCategoriesRecord.HeaderView
      | fieldName | value |
      | name      | Alex2 |
    Then I verify fields on #ProductCategoriesRecord.RecordView
      | fieldName   | value      |
      | description | Great category |
      | list_order  | 5          |

  @create
  Scenario: Product Categories > Create record
    Given  ProductCategories records exist:
      | *name | list_order | description   |
      | Alex1 | 4          | Alex New category |
    Given I open about view and login
    When I go to "ProductCategories" url
    When I click Create button on #ProductCategoriesList header
    When I provide input for #ProductCategoriesDrawer.HeaderView view
      | *        | name  |
      | RecordID | Alex2 |
    When I provide input for #ProductCategoriesDrawer.RecordView view
      | *        | description | list_order | parent_name |
      | RecordID | Alex2       | 123        | Alex1       |
    When I click Save button on #ProductCategoriesDrawer header
    Then I should see *RecordID in #ProductCategoriesList.ListView
    When I click on preview button on *RecordID in #ProductCategoriesList.ListView
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName   | value |
      | name        | Alex2 |
      | description | Alex2 |
      | list_order  | 123   |
      | parent_name | Alex1 |
