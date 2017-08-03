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

  @list @T_34335
  Scenario: Contracts > List View > Preview
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
      | status    | In Progress |

  @list-search @T_34369 @ci-excluded
  Scenario: Contracts > List View > Filter > Search main input
    Given Contracts records exist:
      | *name       | status     | start_date                | end_date                  |
      | Contracts_1 | inprogress | 2017-09-19T19:20:22+00:00 | 2017-10-19T19:20:22+00:00 |
      | Contracts_2 | inprogress | 2017-10-19T19:20:22+00:00 | 2017-11-19T19:20:22+00:00 |
      | Contracts_3 | inprogress | 2017-11-19T19:20:22+00:00 | 2017-12-19T19:20:22+00:00 |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I go to "Contracts" url
    Then I should see *Contracts_1 in #ContractsList.ListView
    Then I should see *Contracts_2 in #ContractsList.ListView
    Then I should see *Contracts_3 in #ContractsList.ListView
    When I search for "Contracts_2" in #ContractsList.FilterView view
    Then I should not see *Contracts_1 in #ContractsList.ListView
    Then I should see *Contracts_2 in #ContractsList.ListView
    Then I should not see *Contracts_3 in #ContractsList.ListView
    Then I verify fields for *Contracts_2 in #ContractsList.ListView
      | fieldName  | value       |
      | name       | Contracts_2 |
      | status     | In Progress |
      | start_date | 10/19/2017  |
      | end_date   | 11/19/2017  |

  @list-edit @T_34342
  Scenario: Contracts > List View > Inline Edit
    Given Contracts records exist:
      | *name      | status     | start_date                | end_date                  |
      | Contract_1 | inprogress | 2017-09-19T19:20:22+00:00 | 2017-10-19T19:20:22+00:00 |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I go to "Contracts" url
    When I click on Edit button for *Contract_1 in #ContractsList.ListView
    When I set values for *Contract_1 in #ContractsList.ListView
      | fieldName  | value             |
      | name       | Contract_1 edited |
      | start_date | 11/10/2020        |
      | end_date   | 12/10/2020        |
    When I click on Cancel button for *Contract_1 in #ContractsList.ListView
    Then I verify fields for *Contract_1 in #ContractsList.ListView
      | fieldName | value      |
      | name      | Contract_1 |
    When I click on Edit button for *Contract_1 in #ContractsList.ListView
    When I set values for *Contract_1 in #ContractsList.ListView
      | fieldName  | value             |
      | name       | Contract_1 edited |
      | start_date | 11/10/2020        |
      | end_date   | 12/10/2020        |
    When I click on Save button for *Contract_1 in #ContractsList.ListView
    Then I verify fields for *Contract_1 in #ContractsList.ListView
      | fieldName  | value             |
      | name       | Contract_1 edited |
      | start_date | 11/10/2020        |
      | end_date   | 12/10/2020        |

  @list-delete @T_34344
  Scenario: Contracts > List View > Delete
    Given Contracts records exist:
      | *name      | status     | start_date                | end_date                  |
      | Contract_1 | inprogress | 2017-09-19T19:20:22+00:00 | 2017-10-19T19:20:22+00:00 |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I go to "Contracts" url
    When I click on Delete button for *Contract_1 in #ContractsList.ListView
    When I Cancel confirmation alert
    Then I should see #ContractsList view
    Then I should see *Contract_1 in #ContractsList.ListView
    When I click on Delete button for *Contract_1 in #ContractsList.ListView
    When I Confirm confirmation alert
    Then I should see #ContractsList view
    Then I should not see *Contract_1 in #ContractsList.ListView

  @delete @T_34341
  Scenario: Contracts >  Record View > Delete
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
    When I Cancel confirmation alert
    Then I should see #Contract_3Record view
    Then I verify fields on #Contract_3Record.HeaderView
      | fieldName | value      |
      | name      | Contract_3 |
    Then I verify fields on #Contract_3Record.RecordView
      | fieldName | value       |
      | status    | In Progress |
    When I open actions menu in #Contract_3Record
    * I choose Delete from actions menu in #Contract_3Record
    When I Confirm confirmation alert
    Then I should see #ContractsList.ListView view
    Then I should not see *Contract_3 in #ContractsList.ListView

  @copy @T_34340
  Scenario: Contracts > Record view > Copy > Cancel
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
    When I provide input for #ContractsRecord.HeaderView view
      | name          |
      | Contract_1234 |
    When I provide input for #ContractsRecord.RecordView view
      | status | start_date | end_date   |
      | Signed | 09/19/2018 | 10/19/2018 |
    When I click Cancel button on #ContractsRecord header
    Then I verify fields on #Contract_3Record.HeaderView
      | fieldName | value      |
      | name      | Contract_3 |
    Then I verify fields on #Contract_3Record.RecordView
      | fieldName  | value       |
      | status     | In Progress |
      | start_date | 09/19/2017  |
      | end_date   | 10/19/2017  |

  @copy @T_34340
  Scenario: Contracts > Record view > Copy > Save
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
    When I provide input for #ContractsRecord.HeaderView view
      | name          |
      | Contract_1234 |
    When I provide input for #ContractsRecord.RecordView view
      | status | start_date | end_date   |
      | Signed | 09/19/2018 | 10/19/2018 |
    When I click Save button on #Contract_3Record header
    Then I verify fields on #ContractsRecord.HeaderView
      | fieldName | value         |
      | name      | Contract_1234 |
    Then I verify fields on #ContractsRecord.RecordView
      | fieldName  | value      |
      | status     | Signed     |
      | start_date | 09/19/2018 |
      | end_date   | 10/19/2018 |

  @edit_cancel @T_34334 @scenario-stress-test
  Scenario: Contracts > Record View > Edit > Cancel
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
    When I provide input for #Contract_3Record.RecordView view
      | description              | reference_code |
      | My new description value | Test123        |
    When I click Cancel button on #Contract_3Record header
    Then I verify fields on #Contract_3Record.HeaderView
      | fieldName | value      |
      | name      | Contract_3 |
    Then I verify fields on #Contract_3Record.RecordView
      | fieldName      | value |
      | description    |       |
      | reference_code |       |

  @edit_save @T_34334
  Scenario: Contracts > Record View > Edit > Save
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
    When I provide input for #Contract_3Record.RecordView view
      | description              | reference_code |
      | My new description value | Test123        |
    When I click Save button on #Contract_3Record header
    Then I verify fields on #Contract_3Record.HeaderView
      | fieldName | value       |
      | name      | Contract_34 |
    Then I verify fields on #Contract_3Record.RecordView
      | fieldName      | value                    |
      | description    | My new description value |
      | reference_code | Test123                  |
    When I go to "Contracts" url
    When I click on preview button on *Contract_3 in #ContractsList.ListView
    Then I should see #Contract_3Preview view
    Then I verify fields on #Contract_3Preview.PreviewView
      | fieldName | value       |
      | name      | Contract_34 |

  @create @T_30161
  Scenario: Contracts > Create record > Cancel/Save
    Given ContractTypes records exist:
      | *name | list_order |
      | Type1 | 30         |
    Given Accounts records exist:
      | *name     |
      | myAccount |
    Given I open about view and login
    When I go to "Contracts" url
    When I click Create button on #ContractsList header
    When I provide input for #ContractsRecord.HeaderView view
      | *        | name         |
      | RecordID | New_Contract |
    When I provide input for #ContractsRecord.RecordView view
      | *        | start_date | end_date   | status | account_name | type_name | reference_code | company_signed_date | customer_signed_date | description   |
      | RecordID | 10/10/2017 | 10/11/2017 | Signed | myAccount    | Type1     | 123test        | 10/01/2017          | 10/02/2017           | Amazing Deal! |
    # Cancel Contract creation
    When I click Cancel button on #ContractsRecord header
    Then I should see #ContractsList.ListView view
    When I go to "Contracts" url
    When I click Create button on #ContractsList header
    When I provide input for #ContractsRecord.HeaderView view
      | *        | name         |
      | RecordID | New_Contract |
    When I provide input for #ContractsRecord.RecordView view
      | *        | start_date | end_date   | status | account_name | type_name | reference_code | company_signed_date | customer_signed_date | description   |
      | RecordID | 10/10/2017 | 10/11/2017 | Signed | myAccount    | Type1     | 123test        | 10/01/2017          | 10/02/2017           | Amazing Deal! |
    # Save Contract record
    When I click Save button on #ContractsRecord header
    Then I should see *RecordID in #ContractsList.ListView
    When I click on preview button on *RecordID in #ContractsList.ListView
    # Verify the new record values in preview
    Then I should see #RecordIDPreview view
    Then I verify fields on #RecordIDPreview.PreviewView
      | fieldName            | value         |
      | name                 | New_Contract  |
      | status               | Signed        |
      | start_date           | 10/10/2017    |
      | end_date             | 10/11/2017    |
      | company_signed_date  | 10/01/2017    |
      | customer_signed_date | 10/02/2017    |
      | description          | Amazing Deal! |
      | account_name         | myAccount     |
      | type_name            | Type1         |




