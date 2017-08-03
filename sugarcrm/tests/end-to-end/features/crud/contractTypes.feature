# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules
Feature: Contract Types module verification

  Background:
    Given I use default account
    Given I launch App

  @list @T_34370
  Scenario: Contract Types > List View > Preview
    Given ContractTypes records exist:
      | *name | list_order |
      | Alex1 | 4          |
    Given I open about view and login
    When I go to "ContractTypes" url
    Then I should see *Alex1 in #ContractTypesList.ListView
    Then I verify fields for *Alex1 in #ContractTypesList.ListView
      | fieldName | value |
      | name      | Alex1 |
    When I click on preview button on *Alex1 in #ContractTypesList.ListView
    Then I should see #Alex1Preview view
    Then I verify fields on #Alex1Preview.PreviewView
      | fieldName  | value |
      | name       | Alex1 |
      | list_order | 4     |

  @list-search @T_34394 @ci-excluded
  Scenario: Contract Types > List View > Filter > Search main input
    Given ContractTypes records exist:
      | *name           |  list_order |
      | ContractTypes_1 |  5          |
      | ContractTypes_2 |  6          |
      | ContractTypes_3 |  7          |
    Given I open about view and login
    When I go to "ContractTypes" url
    # Search for "ContractTypes": 3 records found
    When I search for "ContractTypes" in #ContractTypesList.FilterView view
    Then I should see *ContractTypes_1 in #ContractTypesList.ListView
    Then I should see *ContractTypes_2 in #ContractTypesList.ListView
    Then I should see *ContractTypes_3 in #ContractTypesList.ListView
    # Search for "ContractTypes_2" 1 record found
    When I search for "ContractTypes_2" in #ContractTypesList.FilterView view
    Then I should not see *ContractTypes_1 in #ContractTypesList.ListView
    Then I should see *ContractTypes_2 in #ContractTypesList.ListView
    Then I should not see *ContractTypes_3 in #ContractTypesList.ListView
    Then I verify fields for *ContractTypes_2 in #ContractTypesList.ListView
      | fieldName  | value           |
      | name       | ContractTypes_2 |
      | list_order | 6               |


  @list-edit @T_34395
  Scenario: Contract Types > List View > Inline Edit
    Given ContractTypes records exist:
      | *name | list_order |
      | Alex1 | 4          |
    Given I open about view and login
    When I go to "ContractTypes" url
    When I click on Edit button for *Alex1 in #ContractTypesList.ListView
    When I set values for *Alex1 in #ContractTypesList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
    When I click on Cancel button for *Alex1 in #ContractTypesList.ListView
    Then I verify fields for *Alex1 in #ContractTypesList.ListView
      | fieldName | value |
      | name      | Alex1 |
    When I click on Edit button for *Alex1 in #ContractTypesList.ListView
    When I set values for *Alex1 in #ContractTypesList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
    When I click on Save button for *Alex1 in #ContractTypesList.ListView
    Then I verify fields for *Alex1 in #ContractTypesList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |

  @list-delete @T_34396
  Scenario: ContractTypes > List View > Delete
    Given ContractTypes records exist:
      | *name | list_order |
      | Alex1 | 4          |
    Given I open about view and login
    When I go to "ContractTypes" url
    When I click on Delete button for *Alex1 in #ContractTypesList.ListView
    When I Cancel confirmation alert
    Then I should see #ContractTypesList view
    Then I should see *Alex1 in #ContractTypesList.ListView
    When I click on Delete button for *Alex1 in #ContractTypesList.ListView
    When I Confirm confirmation alert
    Then I should see #ContractTypesList view
    Then I should not see *Alex1 in #ContractTypesList.ListView

  @delete @T_34372
  Scenario: Contract Types > Record View > Delete
    Given ContractTypes records exist:
      | *name | list_order |
      | Alex1 | 4          |
    Given I open about view and login
    When I go to "ContractTypes" url
    When I select *Alex1 in #ContractTypesList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    * I choose Delete from actions menu in #Alex1Record
    When I Cancel confirmation alert
    Then I should see #Alex1Record view
    Then I verify fields on #Alex1Record.HeaderView
      | fieldName | value |
      | name      | Alex1 |
    Then I verify fields on #Alex1Record.RecordView
      | fieldName  | value |
      | list_order | 4     |
    When I open actions menu in #Alex1Record
    * I choose Delete from actions menu in #Alex1Record
    When I Confirm confirmation alert
    Then I should see #ContractTypesList.ListView view
    Then I should not see *Alex1 in #ContractTypesList.ListView

  @copy_cancel @T_34373
  Scenario: Contract Types > Record View > Copy > Cancel
    Given ContractTypes records exist:
      | *name | list_order |
      | Alex1 | 4          |
    Given I open about view and login
    When I go to "ContractTypes" url
    When I select *Alex1 in #ContractTypesList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ContractTypesRecord.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #ContractTypesRecord.RecordView view
      | list_order |
      | 5          |
    When I click Cancel button on #ContractTypesRecord header
    Then I verify fields on #Alex1Record.HeaderView
      | fieldName | value |
      | name      | Alex1 |
    Then I verify fields on #Alex1Record.RecordView
      | fieldName  | value |
      | list_order | 4     |


  @copy_save @T_34373
  Scenario: Contract Types > Record View > Copy > Save
    Given ContractTypes records exist:
      | *name | list_order |
      | Alex1 | 4          |
    Given I open about view and login
    When I go to "ContractTypes" url
    When I select *Alex1 in #ContractTypesList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ContractTypesRecord.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #ContractTypesRecord.RecordView view
      | list_order |
      | 5          |
    When I click Save button on #ContractTypesRecord header
    Then I verify fields on #ContractTypesRecord.HeaderView
      | fieldName | value |
      | name      | Alex2 |
    Then I verify fields on #ContractTypesRecord.RecordView
      | fieldName  | value |
      | list_order | 5     |


  @create @T_31013
  Scenario: Contract Types > Create Record > Cancel/Save
    Given I open about view and login
    When I go to "ContractTypes" url
    When I click Create button on #ContractTypesList header
    When I provide input for #ContractTypesRecord.HeaderView view
      | *         | name  |
      | RecordID1 | Alex3 |
    When I provide input for #ContractTypesRecord.RecordView view
      | *         | list_order |
      | RecordID1 | 123        |
    When I click Cancel button on #ContractTypesRecord header
    Then I should see #ContractTypesList.ListView view
    When I go to "ContractTypes" url
    When I click Create button on #ContractTypesList header
    When I provide input for #ContractTypesRecord.HeaderView view
      | *         | name  |
      | RecordID1 | Alex3 |
    When I provide input for #ContractTypesRecord.RecordView view
      | *         | list_order |
      | RecordID1 | 123        |
    When I click Save button on #ContractTypesRecord header
    Then I should see *RecordID1 in #ContractTypesList.ListView
    When I click on preview button on *RecordID1 in #ContractTypesList.ListView
    Then I should see #RecordID1Preview view
    Then I verify fields on #RecordID1Preview.PreviewView
      | fieldName  | value |
      | name       | Alex3 |
      | list_order | 123   |
