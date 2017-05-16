# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules
Feature: Contracts module verification

  Background:
    Given I use default account
    Given I launch App

  @list
  Scenario: Contracts > List View > Contain pre-created record + Preview
    Given Contracts records exist:
      | *name       | status     |
      | Contracts_1 | inprogress |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I go to "Contracts" url
    Then I should see *Contracts_1 in #ContractsList.ListView
    Then I verify fields for *Contracts_1 in #ContractsList.ListView
      | fieldName | value       |
      | name      | Contracts_1 |
    When I click on preview button on *Contracts_1 in #ContractsList.ListView
    Then I should see #Contracts_1Preview view
    Then I verify fields on #Contracts_1Preview.PreviewView
      | fieldName | value       |
      | name      | Contracts_1 |

  @list-search
  Scenario: Contracts > List View > Filter > Search main input
    Given Contracts records exist:
      | *name       | status     | start_date                | end_date                  |
      | Contracts_2 | inprogress | 2017-09-19T19:20:22+00:00 | 2017-10-19T19:20:22+00:00 |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I go to "Contracts" url
    Then I should see *Contracts_2 in #ContractsList.ListView
    When I search for "Contracts_2" in #ContractsList.FilterView view
    Then I should see *Contracts_2 in #ContractsList.ListView
    Then I verify fields for *Contracts_2 in #ContractsList.ListView
      | fieldName | value       |
      | name      | Contracts_2 |

  @delete
  Scenario: Contracts > Delete > Delete contract from Record View
    Given Contracts records exist:
      | *name      | status     | start_date                | end_date                  |
      | Contract_3 | inprogress | 2017-09-19T19:20:22+00:00 | 2017-10-19T19:20:22+00:00 |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I go to "Contracts" url
    When I select *Contract_3 in #ContractsList.ListView
    Then I should see #Contract_3Record view
    When I open actions menu in #Contract_3Record
    * I choose Delete from actions menu in #Contract_3Record
    When I Confirm confirmation alert
    Then I should see #ContractsList.ListView view
    Then I should not see *Contract_3 in #ContractsList.ListView

  @copy
  Scenario: Contracts > Copy > Copy Contract from Record View
    Given Contracts records exist:
      | *name      | status     | start_date                | end_date                  |
      | Contract_3 | inprogress | 2017-09-19T19:20:22+00:00 | 2017-10-19T19:20:22+00:00 |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I go to "Contracts" url
    When I select *Contract_3 in #ContractsList.ListView
    Then I should see #Contract_3Record view
    When I open actions menu in #Contract_3Record
    When I choose Copy from actions menu in #Contract_3Record
    When I provide input for #Contract_3Record.HeaderView view
      | name          |
      | Contract_1234 |
    When I click Save button on #Contract_3Record header
    Then I verify fields on #Contract_3Record.HeaderView
      | fieldName | value         |
      | name      | Contract_1234 |


  @change_name
  Scenario: Contracts > Record View > Edit > Change Record Name > Save
    Given Contracts records exist:
      | *name      | status     | start_date                | end_date                  |
      | Contract_3 | inprogress | 2017-09-19T19:20:22+00:00 | 2017-10-19T19:20:22+00:00 |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I go to "Contracts" url
    When I select *Contract_3 in #ContractsList.ListView
    Then I should see #Contract_3Record view
    When I open actions menu in #Contract_3Record
    When I click Edit button on #Contract_3Record header
    Then I should see #Contract_3Record view
    When I provide input for #Contract_3Record.HeaderView view
      | name        |
      | Contract_34 |
    When I click Save button on #Contract_3Record header
    Then I verify fields on #Contract_3Record.HeaderView
      | fieldName | value       |
      | name      | Contract_34 |
    When I go to "Contracts" url
    When I click on preview button on *Contract_3 in #ContractsList.ListView
    Then I should see #Contract_3Preview view
    Then I verify fields on #Contract_3Preview.PreviewView
      | fieldName | value       |
      | name      | Contract_34 |

  @change_record_description
  Scenario: Contracts > Record View > Edit > Change field values > Save
    Given Contracts records exist:
      | *name      | status     | start_date                | end_date                  |
      | Contract_4 | inprogress | 2017-09-19T19:20:22+00:00 | 2017-10-19T19:20:22+00:00 |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I go to "Contracts" url
    When I select *Contract_4 in #ContractsList.ListView
    Then I should see #Contract_4Record view
    When I open actions menu in #Contract_4Record
    When I click Edit button on #Contract_4Record header
    Then I should see #Contract_4Record view
    When I click show more button on #Contract_4Record view
    When I provide input for #Contract_4Record.RecordView view
      | description              | reference_code |
      | My new description value | Test123        |
    When I click Save button on #Contract_4Record header
    When I click show less button on #Contract_4Record view
    When I go to "Contracts" url
    When I click on preview button on *Contract_4 in #ContractsList.ListView
    Then I should see #Contract_4Preview view
    Then I verify fields on #Contract_4Preview.PreviewView
      | fieldName      | value                    |
      | description    | My new description value |
      | reference_code | Test123                  |
