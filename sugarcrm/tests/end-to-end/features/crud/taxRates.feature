# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules
Feature: TaxRates module verification

  Background:
    Given I use default account
    Given I launch App

  @list @T_34357
  Scenario: Tax Rates > List View > Preview
    Given TaxRates records exist:
      | *name | list_order | status | value |
      | Alex1 | 4          | Active | 10.51 |
    Given I open about view and login
    When I go to "TaxRates" url
    Then I should see *Alex1 in #TaxRatesList.ListView
    Then I verify fields for *Alex1 in #TaxRatesList.ListView
      | fieldName  | value  |
      | name       | Alex1  |
      | status     | Active |
      | list_order | 4      |
      | value      | 10.51  |
    When I click on preview button on *Alex1 in #TaxRatesList.ListView
    Then I should see #Alex1Preview view
    Then I verify fields on #Alex1Preview.PreviewView
      | fieldName  | value  |
      | name       | Alex1  |
      | status     | Active |
      | list_order | 4      |
      | value      | 10.51  |


  @list-search @T_34391 @ci-excluded
  Scenario: Tax Rates > List View > Filter > Search main input
    Given TaxRates records exist:
      | *name      | status   | list_order | value |
      | TaxRates_1 | Active   | 5          | 2.22  |
      | TaxRates_2 | Inactive | 6          | 3.33  |
      | TaxRates_3 | Active   | 7          | 4.44  |
    Given I open about view and login
    When I go to "TaxRates" url
    # Search for "TaxRates": 3 records found
    When I search for "TaxRates" in #TaxRatesList.FilterView view
    Then I should see *TaxRates_1 in #TaxRatesList.ListView
    Then I should see *TaxRates_2 in #TaxRatesList.ListView
    Then I should see *TaxRates_3 in #TaxRatesList.ListView
    # Search for "TaxRates_2" 1 record found
    When I search for "TaxRates_2" in #TaxRatesList.FilterView view
    Then I should not see *TaxRates_1 in #TaxRatesList.ListView
    Then I should see *TaxRates_2 in #TaxRatesList.ListView
    Then I should not see *TaxRates_3 in #TaxRatesList.ListView
    Then I verify fields for *TaxRates_2 in #TaxRatesList.ListView
      | fieldName  | value      |
      | name       | TaxRates_2 |
      | status     | Inactive   |
      | list_order | 6          |
      | value      | 3.33       |


  @list-edit @T_34392
  Scenario: TaxRates > List View > Inline Edit
    Given TaxRates records exist:
      | *name | list_order | status | value |
      | Alex1 | 4          | Active | 3.33  |
    Given I open about view and login
    When I go to "TaxRates" url
    When I click on Edit button for *Alex1 in #TaxRatesList.ListView
    When I set values for *Alex1 in #TaxRatesList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
      | value      | 3.33         |
    When I click on Cancel button for *Alex1 in #TaxRatesList.ListView
    Then I verify fields for *Alex1 in #TaxRatesList.ListView
      | fieldName | value |
      | name      | Alex1 |
    When I click on Edit button for *Alex1 in #TaxRatesList.ListView
    When I set values for *Alex1 in #TaxRatesList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
      | value      | 3.33         |
    When I click on Save button for *Alex1 in #TaxRatesList.ListView
    Then I verify fields for *Alex1 in #TaxRatesList.ListView
      | fieldName  | value        |
      | name       | Alex1 edited |
      | list_order | 5            |
      | value      | 3.33         |

  @list-delete @T_34393
  Scenario: TaxRates > List View > Delete
    Given TaxRates records exist:
      | *name | list_order | status | value |
      | Alex1 | 4          | Active | 3.33  |
    Given I open about view and login
    When I go to "TaxRates" url
    When I click on Delete button for *Alex1 in #TaxRatesList.ListView
    When I Cancel confirmation alert
    Then I should see #TaxRatesList view
    Then I should see *Alex1 in #TaxRatesList.ListView
    When I click on Delete button for *Alex1 in #TaxRatesList.ListView
    When I Confirm confirmation alert
    Then I should see #TaxRatesList view
    Then I should not see *Alex1 in #TaxRatesList.ListView

  @delete @T_34358
  Scenario: Tax Rates > Record View > Delete
    Given TaxRates records exist:
      | *name | list_order | status | value |
      | Alex1 | 4          | Active | 10.51 |
    Given I open about view and login
    When I go to "TaxRates" url
    When I select *Alex1 in #TaxRatesList.ListView
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
    # Confirm deletion
    * I choose Delete from actions menu in #Alex1Record
    When I Confirm confirmation alert
    Then I should see #TaxRatesList.ListView view
    Then I should not see *Alex1 in #TaxRatesList.ListView

  @copy_cancel @T_34359
  Scenario: Tax Rates > Record View > Copy > Cancel
    Given TaxRates records exist:
      | *name | list_order | status | value |
      | Alex1 | 4          | Active | 10.51 |
    Given I open about view and login
    When I go to "TaxRates" url
    When I select *Alex1 in #TaxRatesList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #Alex1Record.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #Alex1Record.RecordView view
      | list_order | status   | value |
      | 5          | Inactive | 5.52  |
    When I click Cancel button on #TaxRatesRecord header
    Then I verify fields on #Alex1Record.HeaderView
      | fieldName | value |
      | name      | Alex1 |
    Then I verify fields on #Alex1Record.RecordView
      | fieldName  | value  |
      | status     | Active |
      | list_order | 4      |
      | value      | 10.51  |


  @copy_save @T_34359
  Scenario: Tax Rates > Record View > Copy > Save
    Given TaxRates records exist:
      | *name | list_order | status | value |
      | Alex1 | 4          | Active | 10.51 |
    Given I open about view and login
    When I go to "TaxRates" url
    When I select *Alex1 in #TaxRatesList.ListView
    Then I should see #Alex1Record view
    When I open actions menu in #Alex1Record
    When I choose Copy from actions menu in #Alex1Record
    When I provide input for #TaxRatesRecord.HeaderView view
      | *     | name  |
      | Alex2 | Alex2 |
    When I provide input for #TaxRatesRecord.RecordView view
      | list_order | status   | value |
      | 5          | Inactive | 5.52  |
    When I click Save button on #Alex1Record header
    Then I verify fields on #Alex1Record.HeaderView
      | fieldName | value |
      | name      | Alex2 |
    Then I verify fields on #Alex2Record.RecordView
      | fieldName  | value    |
      | status     | Inactive |
      | list_order | 5        |
      | value      | 5.52     |
    When I go to "TaxRates" url
    Then I should see *Alex2 in #TaxRatesList.ListView
    When I click on preview button on *Alex2 in #TaxRatesList.ListView
    Then I should see #Alex2Preview view
    Then I verify fields on #Alex2Preview.PreviewView
      | fieldName  | value    |
      | name       | Alex2    |
      | status     | Inactive |
      | list_order | 5        |
      | value      | 5.52     |

  @create @T_30166
  Scenario: Tax Rates > Create
    Given I open about view and login
    When I go to "TaxRates" url
    When I click Create button on #TaxRatesList header
    When I provide input for #TaxRatesRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex2 |
    When I provide input for #TaxRatesRecord.RecordView view
      | *        | status | list_order | value |
      | RecordID | Active | 123        | 12.12 |
    When I click Save button on #TaxRatesRecord header
    Then I should see *RecordID in #TaxRatesList.ListView
    When I click on preview button on *RecordID in #TaxRatesList.ListView
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName  | value  |
      | name       | Alex2  |
      | status     | Active |
      | list_order | 123    |
      | value      | 12.12  |
