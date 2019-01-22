# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_meetings @job3
Feature: Meetings module verification

  Background:
    Given I use default account
    Given I launch App

  @list
  Scenario: Meetings > List View > Contain pre-created record/ Preview
    # Add regular Meeting
    Given Meetings records exist:
      | *name     | assigned_user_id | date_start                | duration_minutes | reminder_time | email_reminder_time | description           | status  |
      | Meeting_A | 1                | 2020-04-16T14:30:00-07:00 | 45               | 0             | 0                   | Meeting with customer | Planned |
    Given I open about view and login
    When I choose Meetings in modules menu
    Then I should see *Meeting_A in #MeetingsList.ListView
    Then I verify fields for *Meeting_A in #MeetingsList.ListView
      | fieldName | value     |
      | name      | Meeting_A |
    When I click on preview button on *Meeting_A in #MeetingsList.ListView
    Then I should see #Meeting_APreview view
    Then I verify fields on #Meeting_APreview.PreviewView
      | fieldName   | value                 |
      | name        | Meeting_A             |
      | description | Meeting with customer |
      | status      | Scheduled             |
    When I select *Meeting_A in #MeetingsList.ListView
    Then I should see #Meeting_ARecord view


  @list-edit
  Scenario: Meetings > List View > Inline Edit > Cancel/Save
    Given Meetings records exist:
      | *name     | assigned_user_id | date_start                | duration_minutes | reminder_time | email_reminder_time | description           | status  |
      | Meeting_A | 1                | 2020-04-16T14:30:00-07:00 | 45               | 0             | 0                   | Meeting with customer | Planned |
    Given I open about view and login
    When I choose Meetings in modules menu
    Then I should see *Meeting_A in #MeetingsList.ListView
    # Edit meeting > Cancel > Verify
    When I click on Edit button for *Meeting_A in #MeetingsList.ListView
    When I set values for *Meeting_A in #MeetingsList.ListView
      | fieldName | value     |
      | name      | Meeting_B |
      | status    | Canceled  |
    When I click on Cancel button for *Meeting_A in #MeetingsList.ListView
    Then I verify fields for *Meeting_A in #MeetingsList.ListView
      | fieldName | value     |
      | name      | Meeting_A |
      | status    | Scheduled |

    # Edit meeting > Save > Verify
    When I click on Edit button for *Meeting_A in #MeetingsList.ListView
    When I set values for *Meeting_A in #MeetingsList.ListView
      | fieldName | value     |
      | name      | Meeting_B |
      | status    | Canceled  |
    When I click on Save button for *Meeting_A in #MeetingsList.ListView
    When I close alert
    Then I verify fields for *Meeting_A in #MeetingsList.ListView
      | fieldName | value     |
      | name      | Meeting_B |
      | status    | Canceled  |

  @list-delete
  Scenario: Contracts > List View > Delete
    Given Meetings records exist:
      | *name     | assigned_user_id | date_start                | duration_minutes | reminder_time | email_reminder_time | description           | status  |
      | Meeting_A | 1                | 2020-04-16T14:30:00-07:00 | 45               | 0             | 0                   | Meeting with customer | Planned |
    Given I open about view and login
    When I choose Meetings in modules menu
    Then I should see *Meeting_A in #MeetingsList.ListView
    When I click on Delete button for *Meeting_A in #MeetingsList.ListView
    When I Cancel confirmation alert
    Then I should see *Meeting_A in #MeetingsList.ListView
    When I click on Delete button for *Meeting_A in #MeetingsList.ListView
    When I Confirm confirmation alert
    Then I should see #MeetingsList view
    Then I should not see *Meeting_A in #MeetingsList.ListView

  @delete
  Scenario: Meetings >  Record View > Delete
    Given Meetings records exist:
      | *name     | assigned_user_id | date_start                | duration_minutes | reminder_time | email_reminder_time | description           | status  | location |
      | Meeting_A | 1                | 2020-04-16T14:30:00-07:00 | 45               | 0             | 0                   | Meeting with customer | Planned | San Jose |
    Given I open about view and login
    When I choose Meetings in modules menu
    Then I should see *Meeting_A in #MeetingsList.ListView
    When I select *Meeting_A in #MeetingsList.ListView
    Then I should see #Meeting_ARecord view
    When I open actions menu in #Meeting_ARecord
    * I choose Delete from actions menu in #Meeting_ARecord
    When I Cancel confirmation alert
    Then I should see #Meeting_ARecord view
    Then I verify fields on #Meeting_ARecord.HeaderView
      | fieldName | value     |
      | name      | Meeting_A |
    Then I verify fields on #Meeting_ARecord.RecordView
      | fieldName | value    |
      | location  | San Jose |
    When I open actions menu in #Meeting_ARecord
    * I choose Delete from actions menu in #Meeting_ARecord
    When I Confirm confirmation alert
    Then I should see #MeetingsList.ListView view
    Then I should not see *Meeting_A in #MeetingsList.ListView

  @copy
  Scenario: Meetings > Record view > Copy > Cancel/Save
    Given Meetings records exist:
      | *name     | assigned_user_id | date_start                | duration_minutes | reminder_time | email_reminder_time | description           | status  | location |
      | Meeting_A | 1                | 2020-04-16T14:30:00-07:00 | 45               | 0             | 0                   | Meeting with customer | Planned | San Jose |
    Given I open about view and login
    When I choose Meetings in modules menu
    Then I should see *Meeting_A in #MeetingsList.ListView
    When I select *Meeting_A in #MeetingsList.ListView
    Then I should see #Meeting_ARecord view
    # Copy Meeting > Cancel
    When I open actions menu in #Meeting_ARecord
    When I choose Copy from actions menu in #Meeting_ARecord
    When I provide input for #MeetingsDrawer.HeaderView view
      | *name           |
      | NewMeeting_1234 |
    When I provide input for #MeetingsDrawer.RecordView view
      | location |
      | San Jose |
    When I click Cancel button on #MeetingsDrawer header
    Then I verify fields on #Meeting_ARecord.HeaderView
      | fieldName | value     |
      | name      | Meeting_A |
    Then I verify fields on #Meeting_ARecord.RecordView
      | fieldName | value    |
      | location  | San Jose |
    # Copy Meeting > Save
    When I open actions menu in #Meeting_ARecord
    When I choose Copy from actions menu in #Meeting_ARecord
    When I provide input for #MeetingsDrawer.HeaderView view
      | *name           |
      | NewMeeting_1234 |
    When I provide input for #MeetingsDrawer.RecordView view
      | location |
      | San Jose |
    When I click Save button on #MeetingsDrawer header
    When I close alert
    Then I verify fields on #Meeting_ARecord.HeaderView
      | fieldName | value           |
      | name      | NewMeeting_1234 |
    Then I verify fields on #Meeting_ARecord.RecordView
      | fieldName | value    |
      | location  | San Jose |

  @close
  Scenario: Meetings > Record view > Close
    Given Meetings records exist:
      | *name     | assigned_user_id | date_start                | duration_minutes | reminder_time | email_reminder_time | description           | status  | location |
      | Meeting_A | 1                | 2020-04-16T14:30:00-07:00 | 45               | 0             | 0                   | Meeting with customer | Planned | San Jose |
    Given I open about view and login
    When I choose Meetings in modules menu
    Then I should see *Meeting_A in #MeetingsList.ListView
    When I select *Meeting_A in #MeetingsList.ListView
    Then I should see #Meeting_ARecord view
    # Close Meeting
    When I open actions menu in #Meeting_ARecord
    When I choose CloseMeeting from actions menu in #Meeting_ARecord
    Then I verify fields on #Meeting_ARecord.HeaderView
      | fieldName | value     |
      | name      | Meeting_A |
      | status    | Held      |

  @close_and_create_new
  Scenario: Meetings > Record view > Close and Create New
    Given Accounts records exist:
      | *name |
      | Acc_1 |
    Given Meetings records exist:
      | *name     | assigned_user_id | date_start                | duration_minutes | reminder_time | email_reminder_time | description           | status  | location |
      | Meeting_A | 1                | 2020-04-16T14:30:00-07:00 | 45               | 0             | 0                   | Meeting with customer | Planned | San Jose |
    Given I open about view and login
    When I choose Meetings in modules menu
    Then I should see *Meeting_A in #MeetingsList.ListView
    When I select *Meeting_A in #MeetingsList.ListView
    Then I should see #Meeting_ARecord view
    # Close current Meeting and create new
    When I open actions menu in #Meeting_ARecord
    When I choose CloseAndCreateNew from actions menu in #Meeting_ARecord
    Then I check alert
      | type    | message                         |
      | Success | Success Meeting marked as held. |
    When I close alert
    When I provide input for #MeetingsDrawer.HeaderView view
      | *name     |
      | Meeting_B |
    When I provide input for #MeetingsDrawer.RecordView view
      | *         | date_start         | date_end                  | description          | parent_name   | location  |
      | Meeting_B | 12/01/2020-02:00pm | 12/01/2020-03:00pm (1 hr) | Testing with Seedbed | Account,Acc_1 | Cupertino |
    When I click Save button on #MeetingsDrawer header
    When I close alert
    # Verify created meeting
    Then I verify fields on #Meeting_ARecord.HeaderView
      | fieldName | value     |
      | name      | Meeting_A |
      | status    | Held      |
    When I choose Meetings in modules menu
    Then I should see *Meeting_B in #MeetingsList.ListView
    When I click on preview button on *Meeting_B in #MeetingsList.ListView
    Then I should see #Meeting_BPreview view
    Then I verify fields on #Meeting_BPreview.PreviewView
      | fieldName   | value                |
      | name        | Meeting_B            |
      | location    | Cupertino            |
      | description | Testing with Seedbed |
      | status      | Scheduled            |


  @create_new_meeting
  Scenario: Meetings > Create > Cancel/Save
    Given I open about view and login
    When I choose Meetings in modules menu
    # Create Meeting > Cancel
    When I click Create button on #MeetingsList header
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name        |
      | M_1 | New Meeting |
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | date_start         | date_end                  | description     |
      | M_1 | 12/01/2020-02:00pm | 12/01/2020-03:00pm (1 hr) | Testing Seedbed |
    When I click Cancel button on #MeetingsDrawer header
    Then I should see #MeetingsList.ListView view

    # Create Meeting > Save
    When I click Create button on #MeetingsList header
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name        |
      | M_1 | New Meeting |
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | date_start         | date_end                  | description     |
      | M_1 | 12/01/2020-05:00pm | 12/01/2020-06:00pm (1 hr) | Testing Seedbed |
    When I click Save button on #MeetingsDrawer header
    When I close alert
    Then I should see #MeetingsList.ListView view
    Then I should see *M_1 in #MeetingsList.ListView
    When I click on preview button on *M_1 in #MeetingsList.ListView
    Then I should see #M_1Preview view
    Then I verify fields on #M_1Preview.PreviewView
      | fieldName | value       |
      | name      | New Meeting |

  @meeting_add_remove_invitees
  Scenario: Meetings > Add and Remove Invitees
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
    When I choose Meetings in modules menu
    # Create New Meeting
    When I click Create button on #MeetingsList header
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name        |
      | M_1 | New Meeting |
    # Add Invitees
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | date_start         | date_end                  | invitees                                                           |
      | M_1 | 12/01/2020-02:00pm | 12/01/2020-03:00pm (1 hr) | add: *Contact_1, *Lead_1, *Contact_2, *Lead_2, *Contact_3, *Lead_3 |
    # Save Meeting
    When I click Save button on #MeetingsDrawer header
    When I close alert
    Then I should see #MeetingsList.ListView view
    When I click on preview button on *M_1 in #MeetingsList.ListView
    # Verify invitees in preview
    Then I should see #M_1Preview view
    When I click more guests button on #M_1Preview view
    Then I verify fields on #M_1Preview.PreviewView
      | fieldName | value                                                                                                               |
      | name      | New Meeting                                                                                                         |
      | invitees  | Administrator,cFirst_1 cLast_1,cFirst_2 cLast_2,cFirst_3 cLast_3,lFirst_1 lLast_1,lFirst_2 lLast_2,lFirst_3 lLast_3 |
    When I select *M_1 in #MeetingsList.ListView
    Then I should see #M_1Record view
    # Verify invitees in record view
    When I click more guests button on #M_1Record view
    Then I verify fields on #M_1Record.RecordView
      | fieldName | value                                                                                                               |
      | invitees  | Administrator,cFirst_1 cLast_1,cFirst_2 cLast_2,cFirst_3 cLast_3,lFirst_1 lLast_1,lFirst_2 lLast_2,lFirst_3 lLast_3 |
    # Edit Meeting to remove some invitees
    When I click Edit button on #M_1Record header
    When I provide input for #M_1Record.RecordView view
      | description     | invitees                                         |
      | remove invitees | remove: *Contact_1, *Lead_1, *Contact_3, *Lead_3 |
    # Add another invitee
    When I provide input for #M_1Record.RecordView view
      | invitees     |
      | add: *user_1 |
    # Save
    When I click Save button on #M_1Record header
    When I close alert
    # Verify invitees
    Then I verify fields on #M_1Record.RecordView
      | fieldName | value                                                            |
      | invitees  | Administrator,cFirst_2 cLast_2,lFirst_2 lLast_2,uFirst_1 uLast_1 |
