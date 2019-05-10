# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_calls @job2
Feature: Calls module verification

  Background:
    Given I use default account
    Given I launch App

  @list
  Scenario: Calls > List View > Contain pre-created record/ Preview
    # Add regular call
    Given Calls records exist:
      | *name  | assigned_user_id | date_start                | duration_hours | duration_minutes | direction | reminder_time | email_reminder_time | description      | status  |
      | Call_A | 1                | 2020-04-16T14:30:00-07:00 | 0              | 45               | Outbound  | 0             | 0                   | Call to customer | Planned |
    Given I open about view and login
    When I choose Calls in modules menu
    Then I should see *Call_A in #CallsList.ListView
    Then I verify fields for *Call_A in #CallsList.ListView
      | fieldName | value  |
      | name      | Call_A |
    When I click on preview button on *Call_A in #CallsList.ListView
    Then I should see #Call_APreview view
    Then I verify fields on #Call_APreview.PreviewView
      | fieldName   | value            |
      | name        | Call_A           |
      | direction   | Outbound         |
      | description | Call to customer |
      | status      | Scheduled        |
    When I select *Call_A in #CallsList.ListView
    Then I should see #Call_ARecord view


  @list-edit
  Scenario: Calls > List View > Inline Edit > Cancel/Save
    Given Calls records exist:
      | *name  | assigned_user_id | date_start                | duration_hours | duration_minutes | direction | reminder_time | email_reminder_time | description      | status  |
      | Call_A | 1                | 2020-04-16T14:30:00-07:00 | 0              | 45               | Outbound  | 0             | 0                   | Call to customer | Planned |
    Given I open about view and login
    When I choose Calls in modules menu
    Then I should see *Call_A in #CallsList.ListView
    # Edit call > Cancel > Verify
    When I click on Edit button for *Call_A in #CallsList.ListView
    When I set values for *Call_A in #CallsList.ListView
      | fieldName | value    |
      | name      | Call_B   |
      | direction | Inbound  |
      | status    | Canceled |
    When I click on Cancel button for *Call_A in #CallsList.ListView
    Then I verify fields for *Call_A in #CallsList.ListView
      | fieldName | value     |
      | name      | Call_A    |
      | direction | Outbound  |
      | status    | Scheduled |

    # Edit call > Save > Verify
    When I click on Edit button for *Call_A in #CallsList.ListView
    When I set values for *Call_A in #CallsList.ListView
      | fieldName | value    |
      | name      | Call_B   |
      | direction | Inbound  |
      | status    | Canceled |
    When I click on Save button for *Call_A in #CallsList.ListView
    When I close alert
    Then I verify fields for *Call_A in #CallsList.ListView
      | fieldName | value    |
      | name      | Call_B   |
      | direction | Inbound  |
      | status    | Canceled |

  @list-delete
  Scenario: Calls > List View > Delete
    Given Calls records exist:
      | *name  | assigned_user_id | date_start                | duration_hours | duration_minutes | direction | reminder_time | email_reminder_time | description      | status  |
      | Call_A | 1                | 2020-04-16T14:30:00-07:00 | 0              | 45               | Outbound  | 0             | 0                   | Call to customer | Planned |
    Given I open about view and login
    When I choose Calls in modules menu
    Then I should see *Call_A in #CallsList.ListView
    When I click on Delete button for *Call_A in #CallsList.ListView
    When I Cancel confirmation alert
    Then I should see *Call_A in #CallsList.ListView
    When I click on Delete button for *Call_A in #CallsList.ListView
    When I Confirm confirmation alert
    Then I should see #CallsList view
    Then I should not see *Call_A in #CallsList.ListView


  @delete
  Scenario: Calls >  Record View > Delete
    Given Calls records exist:
      | *name  | assigned_user_id | date_start                | duration_hours | duration_minutes | direction | reminder_time | email_reminder_time | description      | status  |
      | Call_A | 1                | 2020-04-16T14:30:00-07:00 | 0              | 45               | Outbound  | 0             | 0                   | Call to customer | Planned |
    Given I open about view and login
    When I choose Calls in modules menu
    Then I should see *Call_A in #CallsList.ListView
    When I select *Call_A in #CallsList.ListView
    Then I should see #Call_ARecord view
    When I open actions menu in #Call_ARecord
    * I choose Delete from actions menu in #Call_ARecord
    When I Cancel confirmation alert
    Then I should see #Call_ARecord view
    Then I verify fields on #Call_ARecord.HeaderView
      | fieldName | value  |
      | name      | Call_A |
    Then I verify fields on #Call_ARecord.RecordView
      | fieldName | value    |
      | direction | Outbound |
    When I open actions menu in #Call_ARecord
    * I choose Delete from actions menu in #Call_ARecord
    When I Confirm confirmation alert
    Then I should see #CallsList.ListView view
    Then I should not see *Call_A in #CallsList.ListView

  @copy
  Scenario: Calls > Record view > Copy > Cancel/Save
    Given Calls records exist:
      | *name  | assigned_user_id | date_start                | duration_hours | duration_minutes | direction | reminder_time | email_reminder_time | description      | status  |
      | Call_A | 1                | 2020-04-16T14:30:00-07:00 | 0              | 45               | Outbound  | 0             | 0                   | Call to customer | Planned |
    Given I open about view and login
    When I choose Calls in modules menu
    Then I should see *Call_A in #CallsList.ListView
    When I select *Call_A in #CallsList.ListView
    Then I should see #Call_ARecord view
    # Copy Call > Cancel
    When I open actions menu in #Call_ARecord
    When I choose Copy from actions menu in #Call_ARecord
    When I provide input for #CallsDrawer.HeaderView view
      | *name        |
      | NewCall_1234 |
    When I provide input for #CallsDrawer.RecordView view
      | direction |
      | Inbound   |
    When I click Cancel button on #CallsDrawer header
    Then I verify fields on #Call_ARecord.HeaderView
      | fieldName | value  |
      | name      | Call_A |
    Then I verify fields on #Call_ARecord.RecordView
      | fieldName | value    |
      | direction | Outbound |
    # Copy Call > Save
    When I open actions menu in #Call_ARecord
    When I choose Copy from actions menu in #Call_ARecord
    When I provide input for #CallsDrawer.HeaderView view
      | *name        |
      | NewCall_1234 |
    When I provide input for #CallsDrawer.RecordView view
      | direction |
      | Inbound   |
    When I click Save button on #CallsDrawer header
    When I close alert
    Then I verify fields on #Call_ARecord.HeaderView
      | fieldName | value        |
      | name      | NewCall_1234 |
    Then I verify fields on #Call_ARecord.RecordView
      | fieldName | value   |
      | direction | Inbound |

  @close
  Scenario: Calls > Record view > Close
    Given Calls records exist:
      | *name  | assigned_user_id | date_start                | duration_hours | duration_minutes | direction | reminder_time | email_reminder_time | description      | status  |
      | Call_A | 1                | 2020-04-16T14:30:00-07:00 | 0              | 45               | Outbound  | 0             | 0                   | Call to customer | Planned |
    Given I open about view and login
    When I choose Calls in modules menu
    Then I should see *Call_A in #CallsList.ListView
    When I select *Call_A in #CallsList.ListView
    Then I should see #Call_ARecord view
    # Close Call
    When I open actions menu in #Call_ARecord
    When I choose CloseCall from actions menu in #Call_ARecord
    Then I verify fields on #Call_ARecord.HeaderView
      | fieldName | value  |
      | name      | Call_A |
      | status    | Held   |

  @close_and_create_new
  Scenario: Calls > Record view > Close and Create New
    Given Accounts records exist:
      | *name |
      | Acc_1 |
    Given Calls records exist:
      | *name  | assigned_user_id | date_start                | duration_hours | duration_minutes | direction | reminder_time | email_reminder_time | description      | status  |
      | Call_A | 1                | 2020-04-16T14:30:00-07:00 | 0              | 45               | Outbound  | 0             | 0                   | Call to customer | Planned |
    Given I open about view and login
    When I choose Calls in modules menu
    Then I should see *Call_A in #CallsList.ListView
    When I select *Call_A in #CallsList.ListView
    Then I should see #Call_ARecord view
    # Close current Call and create new
    When I open actions menu in #Call_ARecord
    When I choose CloseAndCreateNew from actions menu in #Call_ARecord
    Then I check alert
      | type    | message                      |
      | Success | Success Call marked as held. |
    When I close alert
    When I provide input for #CallsDrawer.HeaderView view
      | *name  |
      | Call_B |
    When I provide input for #CallsDrawer.RecordView view
      | *      | duration                                       | description          | parent_name   | direction |
      | Call_B | 12/01/2020-02:00pm ~ 12/01/2020-03:00pm (1 hr) | Testing with Seedbed | Account,Acc_1 | Outbound  |
    When I click Save button on #CallsDrawer header
    When I close alert
    # Verify created call
    Then I verify fields on #Call_ARecord.HeaderView
      | fieldName | value  |
      | name      | Call_A |
      | status    | Held   |
    When I choose Calls in modules menu
    Then I should see *Call_B in #CallsList.ListView
    When I click on preview button on *Call_B in #CallsList.ListView
    Then I should see #Call_BPreview view
    Then I verify fields on #Call_BPreview.PreviewView
      | fieldName   | value                |
      | name        | Call_B               |
      | direction   | Outbound             |
      | description | Testing with Seedbed |
      | status      | Scheduled            |

  @create_new_call
  Scenario: Calls > Create > Cancel/Save
    Given Accounts records exist:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose Calls in modules menu
    # Create Call> Cancel
    When I click Create button on #CallsList header
    When I provide input for #CallsDrawer.HeaderView view
      | *   | name     |
      | C_1 | New Call |
    When I provide input for #CallsDrawer.RecordView view
      | *   | duration                                       | direction | description     | parent_name   |
      | C_1 | 12/01/2020-02:00pm ~ 12/01/2020-03:00pm (1 hr) | Outbound  | Testing Seedbed | Account,Acc_1 |
    When I click Cancel button on #CallsDrawer header
    Then I should see #CallsList.ListView view

    # Save created call and verify
    When I click Create button on #CallsList header
    When I provide input for #CallsDrawer.HeaderView view
      | *   | name     |
      | C_1 | New Call |
    When I provide input for #CallsDrawer.RecordView view
      | *   | duration                                       | direction | description     | parent_name   |
      | C_1 | 12/01/2020-05:00pm ~ 12/01/2020-06:00pm (1 hr) | Outbound  | Testing Seedbed | Account,Acc_1 |
    When I click Save button on #CallsDrawer header
    When I close alert
    Then I should see #CallsList.ListView view
    Then I should see *C_1 in #CallsList.ListView
    When I click on preview button on *C_1 in #CallsList.ListView
    Then I should see #C_1Preview view
    Then I verify fields on #C_1Preview.PreviewView
      | fieldName   | value    |
      | name        | New Call |
      | parent_name | Acc_1    |

  @calls_add_remove_invitees
  Scenario: Calls > Add and Remove Invitees
    # Add New User
    Given Users records exist:
      | *      | status | user_name | user_hash | last_name | first_name | email              |
      | user_1 | Active | user_1    | LOGIN     | uLast_1   | uFirst_1   | user_1@example.org |

    # Add Contact record(s)
    Given 3 Contacts records exist:
      | *                 | first_name       | last_name       | email                                   |
      | Contact_{{index}} | cFirst_{{index}} | cLast_{{index}} | contact_{{index}}@example.org (primary) |

    # Add Lead record(s)
    Given 3 Leads records exist:
      | *              | first_name       | last_name       | email                                |
      | Lead_{{index}} | lFirst_{{index}} | lLast_{{index}} | lead_{{index}}@example.org (primary) |

    Given I open about view and login
    When I choose Calls in modules menu
    # Create New Call
    When I click Create button on #CallsList header
    When I provide input for #CallsDrawer.HeaderView view
      | *   | name     |
      | C_1 | New Call |
    # Add Invitees
    When I provide input for #CallsDrawer.RecordView view
      | *   | duration                                       | invitees                                                           |
      | C_1 | 12/01/2020-02:00pm ~ 12/01/2020-03:00pm (1 hr) | add: *Contact_1, *Lead_1, *Contact_2, *Lead_2, *Contact_3, *Lead_3 |
    # Save call
    When I click Save button on #CallsDrawer header
    When I close alert
    Then I should see #CallsList.ListView view
    When I click on preview button on *C_1 in #CallsList.ListView
    # Verify invitees in preview
    Then I should see #C_1Preview view
    When I click more guests button on #C_1Preview view
    Then I verify fields on #C_1Preview.PreviewView
      | fieldName | value                                                                                                               |
      | name      | New Call                                                                                                            |
      | invitees  | Administrator,cFirst_1 cLast_1,cFirst_2 cLast_2,cFirst_3 cLast_3,lFirst_1 lLast_1,lFirst_2 lLast_2,lFirst_3 lLast_3 |
    When I select *C_1 in #CallsList.ListView
    Then I should see #C_1Record view
    # Verify invitees in record view
    When I click more guests button on #C_1Record view
    Then I verify fields on #C_1Record.RecordView
      | fieldName | value                                                                                                               |
      | invitees  | Administrator,cFirst_1 cLast_1,cFirst_2 cLast_2,cFirst_3 cLast_3,lFirst_1 lLast_1,lFirst_2 lLast_2,lFirst_3 lLast_3 |
    # Edit Call to remove some invitees
    When I click Edit button on #C_1Record header
    When I provide input for #C_1Record.RecordView view
      | description     | invitees                                         |
      | remove invitees | remove: *Contact_1, *Lead_1, *Contact_3, *Lead_3 |
    # Add another invitee
    When I provide input for #C_1Record.RecordView view
      | invitees     |
      | add: *user_1 |
    # Save
    When I click Save button on #C_1Record header
    When I close alert
    # Verify invitees
    Then I verify fields on #C_1Record.RecordView
      | fieldName | value                                                            |
      | invitees  | Administrator,cFirst_2 cLast_2,lFirst_2 lLast_2,uFirst_1 uLast_1 |
