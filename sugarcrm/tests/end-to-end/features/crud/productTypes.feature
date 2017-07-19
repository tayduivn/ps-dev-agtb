# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules
Feature: productTypes module verification

  Background:
    Given I use default account
    Given I launch App

  @list @T_34397
  Scenario: Product Types > List View > Preview
    Given ProductTypes records exist:
      | *name | list_order | description   |
      | Alex1 | 4          | Alex New Type |
    Given I open about view and login
    When I go to "ProductTypes" url
    Then I should see *Alex1 in #ProductTypesList.ListView
    Then I verify fields for *Alex1 in #ProductTypesList.ListView
      | fieldName | value |
      | name      | Alex1 |
    When I click on preview button on *Alex1 in #ProductTypesList.ListView
    Then I should see #Alex1Preview view
    Then I verify fields on #Alex1Preview.PreviewView
      | fieldName   | value         |
      | name        | Alex1         |
      | description | Alex New Type |
      | list_order  | 4             |

  @list-search @T_34398
  Scenario: Product Types > List View > Filter > Search main input
    Given ProductTypes records exist:
      | *name          | list_order | description |
      | ProductTypes_1 | 5          | 5           |
      | ProductTypes_2 | 6          | 6           |
      | ProductTypes_3 | 7          | 7           |
    Given I open about view and login
    When I go to "ProductTypes" url
    # Search for "ProductTypes": 3 records found
    When I search for "ProductTypes" in #ProductTypesList.FilterView view
    Then I should see *ProductTypes_1 in #ProductTypesList.ListView
    Then I should see *ProductTypes_2 in #ProductTypesList.ListView
    Then I should see *ProductTypes_3 in #ProductTypesList.ListView
    # Search for "ProductTypes_2" 1 record found
    When I search for "ProductTypes_2" in #ProductTypesList.FilterView view
    Then I should not see *ProductTypes_1 in #ProductTypesList.ListView
    Then I should see *ProductTypes_2 in #ProductTypesList.ListView
    Then I should not see *ProductTypes_3 in #ProductTypesList.ListView
    Then I verify fields for *ProductTypes_2 in #ProductTypesList.ListView
      | fieldName   | value          |
      | name        | ProductTypes_2 |
      | description | 6              |
      | list_order  | 6              |


  @list-edit @T_34399
  Scenario: Product Types > List View > Inline Edit
    Given ProductTypes records exist:
      | *name | list_order | description |
      | Alex1 | 4          | Prod. Type  |
    Given I open about view and login
    When I go to "ProductTypes" url
    When I click on Edit button for *Alex1 in #ProductTypesList.ListView
    When I set values for *Alex1 in #ProductTypesList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
    When I click on Cancel button for *Alex1 in #ProductTypesList.ListView
    Then I verify fields for *Alex1 in #ProductTypesList.ListView
      | fieldName | value |
      | name      | Alex1 |
    When I click on Edit button for *Alex1 in #ProductTypesList.ListView
    When I set values for *Alex1 in #ProductTypesList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
    When I click on Save button for *Alex1 in #ProductTypesList.ListView
    Then I verify fields for *Alex1 in #ProductTypesList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |

  @list-delete @T_34400
  Scenario: Product Types > List View > Delete
    Given ProductTypes records exist:
      | *name | list_order | status | description |
      | Alex1 | 4          | Active | Prod. Type  |
    Given I open about view and login
    When I go to "ProductTypes" url
    When I click on Delete button for *Alex1 in #ProductTypesList.ListView
    When I Cancel confirmation alert
    Then I should see #ProductTypesList view
    Then I should see *Alex1 in #ProductTypesList.ListView
    When I click on Delete button for *Alex1 in #ProductTypesList.ListView
    When I Confirm confirmation alert
    Then I should see #ProductTypesList view
    Then I should not see *Alex1 in #ProductTypesList.ListView

  @delete @T_34401
  Scenario: Product Type > Record View > Delete
    Given  ProductTypes records exist:
      | *name | list_order | description   |
      | Alex1 | 4          | Alex New Type |
    Given I open about view and login
    When I go to "ProductTypes" url
    When I select *Alex1 in #ProductTypesList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    * I choose Delete from actions menu in #Alex1Record
    When I Confirm confirmation alert
    Then I should see #ProductTypesList.ListView view
    Then I should not see *Alex1 in #ProductTypesList.ListView

  @copy @T_34402
  Scenario: Product Type > Copy > Copy record from Record View
    Given  ProductTypes records exist:
      | *name | list_order | description   |
      | Alex1 | 4          | Alex New Type |
    Given I open about view and login
    When I go to "ProductTypes" url
    When I select *Alex1 in #ProductTypesList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ProductTypesRecord.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #ProductTypesRecord.RecordView view
      | list_order | description |
      | 5          | Great Type  |
    When I click Cancel button on #ProductTypesRecord header
    Then I verify fields on #ProductTypesRecord.HeaderView
      | fieldName | value |
      | name      | Alex1 |
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ProductTypesRecord.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #ProductTypesRecord.RecordView view
      | list_order | description |
      | 5          | Great Type  |
    When I click Save button on #ProductTypesRecord header
    Then I verify fields on #ProductTypesRecord.HeaderView
      | fieldName | value |
      | name      | Alex2 |
    Then I verify fields on #ProductTypesRecord.RecordView
      | fieldName   | value      |
      | description | Great Type |
      | list_order  | 5          |

  @create @T_34403 @ci-excluded
  Scenario: Product Type > Create record
    Given I open about view and login
    When I go to "ProductTypes" url
    When I click Create button on #ProductTypesList header
    When I provide input for #ProductTypesRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex2 |
    When I provide input for #ProductTypesRecord.RecordView view
      | *        | description | list_order |
      | RecordID | Alex2       | 123        |
    When I click Save button on #ProductTypesRecord header
    Then I should see *RecordID in #ProductTypesList.ListView
    When I click on preview button on *RecordID in #ProductTypesList.ListView
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName   | value |
      | name        | Alex2 |
      | description | Alex2 |
      | list_order  | 123   |

