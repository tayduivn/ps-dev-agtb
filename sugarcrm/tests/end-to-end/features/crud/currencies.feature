# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules
Feature: Currrencies module verification

  Background:
    Given I use default account
    Given I launch App

  @list @T_34375
  Scenario: Currencies > List View > Preview
    Given I open about view and login
    When I go to "Currencies" url
    When I click Create button on #CurrenciesList header
    When I provide input for #CurrenciesRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex1 |
    When I provide input for #CurrenciesRecord.RecordView view
      | *        | symbol | conversion_rate | status |
      | RecordID | F      | 1.25            | Active |
    When I click Save button on #TaxRatesRecord header
    Then I should see *RecordID in #CurrenciesList.ListView
    Then I verify fields for *RecordID in #CurrenciesList.ListView
      | fieldName | value |
      | name      | Alex1 |
    When I click on preview button on *RecordID in #ContractsList.ListView
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName       | value  |
      | name            | Alex1  |
      | symbol          | F      |
      | conversion_rate | 1.25   |
      | status          | Active |

  @list-edit @T_34384
  Scenario: Currencies > List View > Inline Edit > Cancel/Save
    Given I open about view and login
    When I go to "Currencies" url
    When I click Create button on #CurrenciesList header
    When I provide input for #CurrenciesRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex1 |
    When I provide input for #CurrenciesRecord.RecordView view
      | *        | symbol | conversion_rate | status |
      | RecordID | F      | 1.25            | Active |
    When I click Save button on #TaxRatesRecord header
    When I close alert
    Then I should see *RecordID in #CurrenciesList.ListView
    When I click on Edit button for *RecordID in #CurrenciesList.ListView
    When I set values for *RecordID in #CurrenciesList.ListView
      | fieldName       | value      |
      | name            | Alex2_edit |
      | conversion_rate | 1.50       |
      | status          | Inactive   |
    When I click on Cancel button for *RecordID in #CurrenciesList.ListView
    Then I verify fields for *RecordID in #CurrenciesList.ListView
      | fieldName | value |
      | name      | Alex1 |
    When I click on Edit button for *RecordID in #CurrenciesList.ListView
    When I set values for *RecordID in #CurrenciesList.ListView
      | fieldName       | value        |
      | name            | Alex2_edited |
      | conversion_rate | 1.50         |
      | status          | Inactive     |
    When I click on Save button for *RecordID in #CurrenciesList.ListView
    Then I verify fields for *RecordID in #CurrenciesList.ListView
      | fieldName       | value        |
      | name            | Alex2_edited |
      | conversion_rate | 1.5          |
      | status          | Inactive     |

  @edit_cancel @T_32764
  Scenario: Currencies > Record view > Edit > Cancel
    Given I open about view and login
    When I go to "Currencies" url
    When I click Create button on #CurrenciesList header
    When I provide input for #CurrenciesRecord.HeaderView view
      | *     | name  |
      | Alex1 | Alex1 |
    When I provide input for #CurrenciesRecord.RecordView view
      | *     | symbol | conversion_rate | status |
      | Alex1 | F      | 1.25            | Active |
    When I click Save button on #TaxRatesRecord header
    When I select *Alex1 in #TaxRatesList.ListView
    When I close alert
    Then I should see #Alex1Record view
    When I click Edit button on #Alex1Record header
    When I provide input for #Alex1Record.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #Alex1Record.RecordView view
      | conversion_rate | symbol | status   |
      | 5               | E      | Inactive |
    When I click Cancel button on #Alex1Record header
    Then I verify fields on #Alex1Record.HeaderView
      | fieldName | value |
      | name      | Alex1 |
    Then I verify fields on #Alex1Record.RecordView
      | fieldName       | value  |
      | status          | Active |
      | symbol          | F      |
      | conversion_rate | 1.25   |


  @edit_save @T_32764
  Scenario: Currencies > Record view > Edit > Save
    Given I open about view and login
    When I go to "Currencies" url
    When I click Create button on #CurrenciesList header
    When I provide input for #CurrenciesRecord.HeaderView view
      | *     | name  |
      | Alex1 | Alex1 |
    When I provide input for #CurrenciesRecord.RecordView view
      | *     | symbol | conversion_rate | status |
      | Alex1 | F      | 1.25            | Active |
    When I click Save button on #TaxRatesRecord header
    When I select *Alex1 in #TaxRatesList.ListView
    When I close alert
    Then I should see #Alex1Record view
    When I click Edit button on #Alex1Record header
    When I provide input for #Alex1Record.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #Alex1Record.RecordView view
      | conversion_rate | symbol | status   |
      | 1.5             | E      | Inactive |
    When I click Save button on #Alex1Record header
    Then I verify fields on #Alex1Record.HeaderView
      | fieldName | value |
      | name      | Alex2 |
    Then I verify fields on #Alex1Record.RecordView
      | fieldName       | value    |
      | status          | Inactive |
      | symbol          | E        |
      | conversion_rate | 1.5      |


  @create @T_34374
  Scenario: Currencies > Create record
    Given I open about view and login
    When I go to "Currencies" url
    When I click Create button on #CurrenciesList header
    When I provide input for #CurrenciesRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex1 |
    When I provide input for #CurrenciesRecord.RecordView view
      | *        | symbol | conversion_rate | status |
      | RecordID | F      | 1.25            | Active |
    When I click Save button on #CurrenciesRecord header
    Then I should see *RecordID in #CurrenciesList.ListView
    When I click on preview button on *RecordID in #CurrenciesList.ListView
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName       | value  |
      | name            | Alex1  |
      | symbol          | F      |
      | conversion_rate | 1.25   |
      | status          | Active |


  @crate_iso4217 @T_34345
  Scenario: Currencies > Create record with iso4217 code
    Given I open about view and login
    When I go to "Currencies" url
    When I click Create button on #CurrenciesList header
    When I provide input for #CurrenciesRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex1 |
    # Japanese Yen
    When I provide input for #CurrenciesRecord.RecordView view
      | *        | iso4217 | conversion_rate | status |
      | RecordID | JPY     | 1.25            | Active |
    Then I verify fields on #CurrenciesRecord.HeaderView
      | fieldName | value |
      | name      | Yen   |
    Then I verify fields on #CurrenciesRecord.RecordView
      | fieldName | value |
      | symbol    | ¥     |
    # Russian Rubbles
    When I provide input for #CurrenciesRecord.RecordView view
      | *        | iso4217 | conversion_rate | status |
      | RecordID | RUB     | 1.26            | Active |
    Then I verify fields on #CurrenciesRecord.HeaderView
      | fieldName | value  |
      | name      | Rubles |
    Then I verify fields on #CurrenciesRecord.RecordView
      | fieldName | value |
      | symbol    | руб   |
    When I click Save button on #CurrenciesRecord header
    Then I should see *RecordID in #CurrenciesList.ListView
    When I click on preview button on *RecordID in #CurrenciesList.ListView
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName       | value  |
      | name            | Rubles |
      | symbol          | руб    |
      | conversion_rate | 1.26   |
      | status          | Active |


