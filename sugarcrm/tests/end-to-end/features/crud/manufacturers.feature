# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules
Feature: Manufacturers module verification

  Background:
    Given I use default account
    Given I launch App

  @list @T_34364
  Scenario: Manufacturers > List View > Preview
    Given  Manufacturers records exist:
      | *name | list_order | status |
      | Alex1 | 4          | Active |
    Given I open about view and login
    When I go to "Manufacturers" url
    Then I should see *Alex1 in #ManufacturersList.ListView
    Then I verify fields for *Alex1 in #ManufacturersList.ListView
      | fieldName | value |
      | name      | Alex1 |
    When I click on preview button on *Alex1 in #ManufacturersList.ListView
    Then I should see #Alex1Preview view
    Then I verify fields on #Alex1Preview.PreviewView
      | fieldName  | value  |
      | name       | Alex1  |
      | status     | Active |
      | list_order | 4      |

  @list-search @T_34385 @ci-excluded
  Scenario: Manufacturers > List View > Filter > Search main input
    Given Manufacturers records exist:
      | *name           | status   | list_order |
      | Manufacturers_1 | Active   | 5          |
      | Manufacturers_2 | Inactive | 6          |
      | Manufacturers_3 | Active   | 7          |
    Given I open about view and login
    When I go to "Manufacturers" url
    # Search for "Manufacturers": 3 records found
    When I search for "Manufacturers" in #ManufacturersList.FilterView view
    Then I should see *Manufacturers_1 in #ManufacturersList.ListView
    Then I should see *Manufacturers_2 in #ManufacturersList.ListView
    Then I should see *Manufacturers_3 in #ManufacturersList.ListView
    # Search for "Manufacturers_2" 1 record found
    When I search for "Manufacturers_2" in #ManufacturersList.FilterView view
    Then I should not see *Manufacturers_1 in #ManufacturersList.ListView
    Then I should see *Manufacturers_2 in #ManufacturersList.ListView
    Then I should not see *Manufacturers_3 in #ManufacturersList.ListView
    Then I verify fields for *Manufacturers_2 in #ManufacturersList.ListView
      | fieldName  | value           |
      | name       | Manufacturers_2 |
      | status     | Inactive        |
      | list_order | 6               |


  @list-edit @T_34386
  Scenario: Manufacturers > List View > Inline Edit
    Given Manufacturers records exist:
      | *name | list_order | status |
      | Alex1 | 4          | Active |
    Given I open about view and login
    When I go to "Manufacturers" url
    When I click on Edit button for *Alex1 in #ManufacturersList.ListView
    When I set values for *Alex1 in #ManufacturersList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
    When I click on Cancel button for *Alex1 in #ManufacturersList.ListView
    Then I verify fields for *Alex1 in #ManufacturersList.ListView
      | fieldName | value |
      | name      | Alex1 |
    When I click on Edit button for *Alex1 in #ManufacturersList.ListView
    When I set values for *Alex1 in #ManufacturersList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
    When I click on Save button for *Alex1 in #ManufacturersList.ListView
    Then I verify fields for *Alex1 in #ManufacturersList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |

  @list-delete @T_34387 @ci-excluded
  Scenario: Manufacturers > List View > Delete
    Given Manufacturers records exist:
      | *name | list_order | status |
      | Alex1 | 4          | Active |
    Given I open about view and login
    When I go to "Manufacturers" url
    When I click on Delete button for *Alex1 in #ManufacturersList.ListView
    When I Cancel confirmation alert
    Then I should see #ManufacturersList view
    Then I should see *Alex1 in #ManufacturersList.ListView
    When I click on Delete button for *Alex1 in #ManufacturersList.ListView
    When I Confirm confirmation alert
    Then I should see #ManufacturersList view
    Then I should not see *Alex1 in #ManufacturersList.ListView

  @delete @T_34365
  Scenario: Manufacturers > Record View > Delete
    Given  Manufacturers records exist:
      | *name | list_order | status |
      | Alex1 | 4          | Active |
    Given I open about view and login
    When I go to "Manufacturers" url
    When I select *Alex1 in #ManufacturersList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    * I choose Delete from actions menu in #Alex1Record
    # Cancel deletion
    When I Cancel confirmation alert
    # Verify record is not deleted
    Then I should see #Alex1Record view
    Then I verify fields on #Alex1Record.HeaderView
      | fieldName | value |
      | name      | Alex1 |
    When I open actions menu in #Alex1Record
    * I choose Delete from actions menu in #Alex1Record
    When I Confirm confirmation alert
    Then I should see #ManufacturersList.ListView view
    Then I should not see *Alex1 in #ManufacturersList.ListView

  @copy_cancel @T_34366
  Scenario: Manufacturers > Record View > Copy > Cancel
    Given  Manufacturers records exist:
      | *name | list_order | status |
      | Alex1 | 4          | Active |
    Given I open about view and login
    When I go to "Manufacturers" url
    When I select *Alex1 in #ManufacturersList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ManufacturersRecord.HeaderView view
      | *     | name  |
      | Alex2 | Alex2 |
    When I provide input for #ManufacturersRecord.RecordView view
      | list_order | status   |
      | 5          | Inactive |
    # Cancel copying record
    When I click Cancel button on #ManufacturersRecord header
    Then I verify fields on #Alex1Record.HeaderView
      | fieldName | value |
      | name      | Alex1 |
    Then I verify fields on #Alex1Record.RecordView
      | fieldName  | value  |
      | status     | Active |
      | list_order | 4      |


  @copy_save @T_34366
  Scenario: Manufacturers > Record View > Copy > Save
    Given  Manufacturers records exist:
      | *name | list_order | status |
      | Alex1 | 4          | Active |
    Given I open about view and login
    When I go to "Manufacturers" url
    When I select *Alex1 in #ManufacturersList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ManufacturersRecord.HeaderView view
      | *     | name  |
      | Alex2 | Alex2 |
    When I provide input for #ManufacturersRecord.RecordView view
      | list_order | status   |
      | 5          | Inactive |
    When I click Save button on #ManufacturersRecord header
    Then I verify fields on #Alex1Record.HeaderView
      | fieldName | value |
      | name      | Alex2 |
    Then I verify fields on #Alex1Record.RecordView
      | fieldName  | value    |
      | status     | Inactive |
      | list_order | 5        |
    When I go to "Manufacturers" url
    Then I should see *Alex2 in #ManufacturersList.ListView
    When I click on preview button on *Alex2 in #ManufacturersList.ListView
    Then I should see #Alex2Preview view
    Then I verify fields on #Alex2Preview.PreviewView
      | fieldName  | value    |
      | name       | Alex2    |
      | status     | Inactive |
      | list_order | 5        |


  @create @T_34367
  Scenario: Manufacturers > Create record
    Given I open about view and login
    When I go to "Manufacturers" url
    When I click Create button on #ManufacturersList header
    When I provide input for #ManufacturersRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex2 |
    When I provide input for #ManufacturersRecord.RecordView view
      | *        | status | list_order |
      | RecordID | Active | 123        |
    When I click Cancel button on #ManufacturersRecord header
    # TODO: find the way to verify that record is not created if creation is canceled
    # Then I should not see *Alex2 in #ManufacturersList.ListView
    When I click Create button on #ManufacturersList header
    When I provide input for #ManufacturersRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex2 |
    When I provide input for #ManufacturersRecord.RecordView view
      | *        | status | list_order |
      | RecordID | Active | 123        |
    When I click Save button on #ManufacturersRecord header
    Then I should see *RecordID in #ManufacturersList.ListView
    When I click on preview button on *RecordID in #ManufacturersList.ListView
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName  | value  |
      | name       | Alex2  |
      | status     | Active |
      | list_order | 123    |
