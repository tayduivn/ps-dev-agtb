# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules
Feature: ShippingProviders module verification

  Background:
    Given I use default account
    Given I launch App

  @list @T_34360
  Scenario: Shippers > List View > Preview
    Given  Shippers records exist:
      | *name | list_order | status |
      | Alex1 | 4          | Active |
    Given I open about view and login
    When I go to "Shippers" url
    Then I should see *Alex1 in #ShippersList.ListView
    Then I verify fields for *Alex1 in #ShippersList.ListView
      | fieldName  | value  |
      | name       | Alex1  |
      | status     | Active |
      | list_order | 4      |
    When I click on preview button on *Alex1 in #ShippersList.ListView
    Then I should see #Alex1Preview view
    Then I verify fields on #Alex1Preview.PreviewView
      | fieldName  | value  |
      | name       | Alex1  |
      | status     | Active |
      | list_order | 4      |

  @list-search @T_34388 @ci-excluded
  Scenario: Shippers > List View > Filter > Search main input
    Given Shippers records exist:
      | *name      | status   | list_order |
      | Shippers_1 | Active   | 5          |
      | Shippers_2 | Inactive | 6          |
      | Shippers_3 | Active   | 7          |
    Given I open about view and login
    When I go to "Shippers" url
    # Search for "Shippers": 3 records found
    When I search for "Shippers" in #ShippersList.FilterView view
    Then I should see *Shippers_1 in #ShippersList.ListView
    Then I should see *Shippers_2 in #ShippersList.ListView
    Then I should see *Shippers_3 in #ShippersList.ListView
    # Search for "Shippers_2" 1 record found
    When I search for "Shippers_2" in #ShippersList.FilterView view
    Then I should not see *Shippers_1 in #ShippersList.ListView
    Then I should see *Shippers_2 in #ShippersList.ListView
    Then I should not see *Shippers_3 in #ShippersList.ListView
    Then I verify fields for *Shippers_2 in #ShippersList.ListView
      | fieldName  | value      |
      | name       | Shippers_2 |
      | status     | Inactive   |
      | list_order | 6          |


  @list-edit @T_34389
  Scenario: Shippers > List View > Inline Edit
    Given Shippers records exist:
      | *name | list_order | status |
      | Alex1 | 4          | Active |
    Given I open about view and login
    When I go to "Shippers" url
    When I click on Edit button for *Alex1 in #ShippersList.ListView
    When I set values for *Alex1 in #ShippersList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
    When I click on Cancel button for *Alex1 in #ShippersList.ListView
    Then I verify fields for *Alex1 in #ShippersList.ListView
      | fieldName | value |
      | name      | Alex1 |
    When I click on Edit button for *Alex1 in #ShippersList.ListView
    When I set values for *Alex1 in #ShippersList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
    When I click on Save button for *Alex1 in #ShippersList.ListView
    Then I verify fields for *Alex1 in #ShippersList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |

  @list-delete @T_34390
  Scenario: Shippers > List View > Delete
    Given Shippers records exist:
      | *name | list_order | status |
      | Alex1 | 4          | Active |
    Given I open about view and login
    When I go to "Shippers" url
    When I click on Delete button for *Alex1 in #ShippersList.ListView
    When I Cancel confirmation alert
    Then I should see #ShippersList view
    Then I should see *Alex1 in #ShippersList.ListView
    When I click on Delete button for *Alex1 in #ShippersList.ListView
    When I Confirm confirmation alert
    Then I should see #ShippersList view
    Then I should not see *Alex1 in #ShippersList.ListView


  @delete @T_34361
  Scenario: Shipping Providers > Record View > Delete
    Given  Shippers records exist:
      | *name | list_order | description   |
      | Alex1 | 4          | Alex New Type |
    Given I open about view and login
    When I go to "Shippers" url
    When I select *Alex1 in #ShippersList.ListView
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
    # Confirm deletion
    When I Confirm confirmation alert
    Then I should see #ShippersList.ListView view
    Then I should not see *Alex1 in #ShippersList.ListView

  @copy_cancel @T_34362
  Scenario: Shipping Providers > Record View > Copy > Cancel
    Given Shippers records exist:
      | *name | list_order | status |
      | Alex1 | 4          | Active |
    Given I open about view and login
    When I go to "Shippers" url
    When I select *Alex1 in #ShippersList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ShippersRecord.HeaderView view
      | *     | name  |
      | Alex2 | Alex2 |
    When I provide input for #ShippersRecord.RecordView view
      | status   | list_order |
      | Inactive | 5          |
    # Cancel copying record
    When I click Cancel button on #ShippersRecord header
    Then I verify fields on #Alex1Record.HeaderView
      | fieldName | value |
      | name      | Alex1 |
    Then I verify fields on #Alex1Record.RecordView
      | fieldName  | value  |
      | status     | Active |
      | list_order | 4      |


  @copy_save @T_34362
  Scenario: Shipping Providers > Record View > Copy > Confirm
    Given Shippers records exist:
      | *name | list_order | status |
      | Alex1 | 4          | Active |
    Given I open about view and login
    When I go to "Shippers" url
    When I select *Alex1 in #ShippersList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #ShippersRecord.HeaderView view
      | *     | name  |
      | Alex2 | Alex2 |
    When I provide input for #ShippersRecord.RecordView view
      | status   | list_order |
      | Inactive | 5          |
    When I click Save button on #ShippersRecord header
    Then I verify fields on #Alex1Record.HeaderView
      | fieldName | value |
      | name      | Alex2 |
    Then I verify fields on #Alex1Record.RecordView
      | fieldName  | value    |
      | status     | Inactive |
      | list_order | 5        |
    When I go to "Shippers" url
    Then I should see *Alex2 in #ShippersList.ListView
    When I click on preview button on *Alex2 in #ShippersList.ListView
    Then I should see #Alex2Preview view
    Then I verify fields on #Alex2Preview.PreviewView
      | fieldName  | value    |
      | name       | Alex2    |
      | status     | Inactive |
      | list_order | 5        |

  @create @T_34363
  Scenario: Shipping Providers > Create Record > Cancel/Save
    Given I open about view and login
    When I go to "Shippers" url
    When I click Create button on #ShippersList header
    When I provide input for #ShippersRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex2 |
    When I provide input for #ShippersRecord.RecordView view
      | *        | status | list_order |
      | RecordID | Active | 123        |
    # Cancel record creation
    When I click Cancel button on #ShippersRecord header
    Then I should see #ShippersList.ListView view
    # TODO: find the way to verify that record is not created if creation is canceled
    # Then I should not see *Alex2 in #ShippersList.ListView
    When I click Create button on #ShippersList header
    When I provide input for #ShippersRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex2 |
    When I provide input for #ShippersRecord.RecordView view
      | *        | status | list_order |
      | RecordID | Active | 123        |
    When I click Save button on #ShippersRecord header
    Then I should see *RecordID in #ShippersList.ListView
    When I click on preview button on *RecordID in #ShippersList.ListView
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName  | value  |
      | name       | Alex2  |
      | status     | Active |
      | list_order | 123    |
