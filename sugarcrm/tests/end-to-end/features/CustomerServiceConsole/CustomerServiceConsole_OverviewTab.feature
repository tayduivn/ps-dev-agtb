# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @job4
Feature: Customer Service Console Verification > Overview Tab
  As a customer service agent I need to be able to verify main CS console functionality

  Background:
    Given I am logged in


  @user_profile
  Scenario: User Profile > Change license type
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Serve" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value       |
      | Sugar Serve |
    When I click on Cancel button on #UserProfile


  @service_console @dashlets_verification
  Scenario: Service Console > Overview Tab > Dashlets screenshots verification

    # Create required Case and Account records
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
    And Cases records exist related via cases link to *A_1:
      | *   | name   | source   | priority | status        | follow_up_datetime        | assigned_user_id |
      | C_1 | Case_1 | Internal | P1       | New           | 2025-11-20T22:27:00+00:00 | 1                |
      | C_2 | Case_2 | Forum    | P2       | Assigned      | 2026-10-20T22:27:00+00:00 | 1                |
      | C_3 | Case_3 | Web      | P3       | Pending Input | 2027-09-20T22:27:00+00:00 | 1                |
      | C_4 | Case_4 | Web      | P1       | Closed        | 2028-08-20T22:27:00+00:00 | 1                |
      | C_5 | Case_5 | Forum    | P2       | Assigned      | 2029-07-20T22:27:00+00:00 | 1                |

    # Navigate to Service Console
    When I choose Home in modules menu and select "Service Console" menu item

    # Verify chart in "My Open Cases by Follow Up Date" dashlet
    # Then I verify that first_row_left_dashlet element from #ServiceConsoleView still looks like myOpenCasesByFollowUpDate

    # Verify chart in "My Open Cases by Status" dashlet
    # Then I verify that second_row_left_dashlet element from #ServiceConsoleView still looks like myOpenCasesByStatus

    # Verify chart in "My Cases in the Last Week by Status" dashlet
    # Then I verify that second_row_right_dashlet element from #ServiceConsoleView still looks like myCasesInTheLastWeekByStatus

    # Verify chart in "Open Cases By User By Status" dashlet
    # Then I verify that third_row_left_dashlet element from #ServiceConsoleView still looks like openCasesByUserByStatus


  @service_console @status_of_open_tasks_assigned_by_me
  Scenario: Service Console > Overview Tab > Status Of Open Tasks assigned by Me

    # Create required Case and Account records
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
    And Cases records exist related via cases link to *A_1:
      | *   | name   | source   | priority | status | follow_up_datetime        | assigned_user_id |
      | C_1 | Case_1 | Internal | P1       | New    | 2025-11-20T22:27:00+00:00 | 1                |

    Given Tasks records exist related via tasks link to *C_1:
      | *  | name   | status        | priority | date_start          | date_due            | description   | assigned_user_id |
      | T1 | Task 1 | Not Started   | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Not Started   | 1                |
      | T2 | Task 2 | In Progress   | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | In Progress   | 1                |
      | T3 | Task 3 | Completed     | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Completed     | 1                |
      | T4 | Task 4 | Pending Input | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Pending Input | 1                |
      | T5 | Task 5 | Deferred      | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Deferred      | 1                |

        # Add New User
    Given 3 Users records exist:
      | *              | status | user_name      | user_hash | last_name       | first_name       | email                      |
      | user_{{index}} | Active | user_{{index}} | LOGIN     | uLast_{{index}} | uFirst_{{index}} | user_{{index}}@example.org |

    # Change 'assigned to' field
    When I perform mass update of Tasks [*T1, *T3] with the following values:
      | fieldName          | value            |
      | assigned_user_name | uFirst_1 uLast_1 |

    And I perform mass update of Tasks [*T2, *T5] with the following values:
      | fieldName          | value            |
      | assigned_user_name | uFirst_2 uLast_2 |

    And I perform mass update of Tasks [*T4] with the following values:
      | fieldName          | value            |
      | assigned_user_name | uFirst_3 uLast_3 |

    # Navigate to Service Console
    When I choose Home in modules menu and select "Service Console" menu item

    # Verify chart in "Status of Open Tasks assigned to me" dashlet
    # Then I verify that third_row_right_dashlet element from #ServiceConsoleView still looks like StatusOfOpenTasksAssignedByMe


  @planned_activities_dashlet
  Scenario: Service Console > Overview Tab > Planned Activities dashlet

    # Add New User
    Given Users records exist:
      | *      | status | user_name | user_hash | last_name | first_name | email              |
      | user_1 | Active | user_1    | LOGIN     | uLast_1   | uFirst_1   | user_1@example.org |

    # Add today's meeting
    And Meetings records exist:
      | *      | name            | assigned_user_id | date_start | duration_minutes | reminder_time | email_reminder_time | description           | status  |
      | TodayM | Today's Meeting | 1                | now        | 45               | 0             | 0                   | Meeting with customer | Planned |

    # Add today's Call
    And Calls records exist:
      | *      | name         | assigned_user_id | date_start | duration_hours | duration_minutes | direction | reminder_time | email_reminder_time | description      | status  |
      | TodayC | Today's Call | 1                | now        | 0              | 45               | Outbound  | 0             | 0                   | Call to customer | Planned |


    # Navigate to Service Console
    When I choose Home in modules menu and select "Service Console" menu item

    # Schedule meeting 'Meeting 1' as Admin
    When I Schedule Meeting in #ServiceConsoleView.PlannedActivitiesDashlet
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name               |
      | M_1 | Initial discussion |
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | duration                                       | description          | invitees     |
      | M_1 | 12/01/2025-05:00pm ~ 12/01/2025-06:00pm (1 hr) | Testing with Seedbed | add: *user_1 |

    When I click Save button on #MeetingsDrawer header
    When I close alert

    # Schedule meeting 'Meeting 2' as Admin
    When I Schedule Meeting in #ServiceConsoleView.PlannedActivitiesDashlet
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name              |
      | M_2 | Follow Up meeting |
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | duration                                       | description          | invitees     |
      | M_2 | 12/15/2025-02:00pm ~ 12/15/2025-03:00pm (1 hr) | Testing with Seedbed | add: *user_1 |
    When I click Save button on #MeetingsDrawer header
    When I close alert

    # Navigate to meetings tab of Planned Activities Dashlet
    When I navigate to Meetings tab in #ServiceConsoleView.PlannedActivitiesDashlet

    # Set Filter to 'today'
    When I set filter as Today in #ServiceConsoleView.PlannedActivitiesDashlet

    # Verify number of records displayed on the tab
    Then I verify the record count in Meetings tab is equal to 1 in #ServiceConsoleView.PlannedActivitiesDashlet

    # Verify that meeting record is present in the meetings tab of the dashlet
    Then I verify *TodayM record info in #ServiceConsoleView.PlannedActivitiesDashlet.ActivitiesList
      | fieldName | value           |
      | name      | Today's Meeting |
      | label     | Overdue         |

    # Set Filter to future
    When I set filter as Future in #ServiceConsoleView.PlannedActivitiesDashlet

    # Set visibility to 'user'
    When I set visibility as 'user' in #ServiceConsoleView.PlannedActivitiesDashlet

    # Verify number of records displayed on the tab
    Then I verify the record count in Meetings tab is equal to 2 in #ServiceConsoleView.PlannedActivitiesDashlet

    # Verify that meeting record is present in the meetings tab of the dashlet
    Then I verify *M_1 record info in #ServiceConsoleView.PlannedActivitiesDashlet.ActivitiesList
      | fieldName | value              |
      | name      | Initial discussion |

    # Verify that meeting record is present in the meetings tab of the dashlet
    Then I verify *M_2 record info in #ServiceConsoleView.PlannedActivitiesDashlet.ActivitiesList
      | fieldName | value             |
      | name      | Follow Up meeting |

    # Set visibility to 'group'
    When I set visibility as 'group' in #ServiceConsoleView.PlannedActivitiesDashlet
    # Verify number of records displayed on the tab
    Then I verify the record count in Meetings tab is equal to 2 in #ServiceConsoleView.PlannedActivitiesDashlet

    # Verify that meeting record is present in the meetings tab of the dashlet
    Then I verify *M_1 record info in #ServiceConsoleView.PlannedActivitiesDashlet.ActivitiesList
      | fieldName | value              |
      | name      | Initial discussion |

    # Verify that meeting record is present in the meetings tab of the dashlet
    Then I verify *M_2 record info in #ServiceConsoleView.PlannedActivitiesDashlet.ActivitiesList
      | fieldName | value             |
      | name      | Follow Up meeting |

    # Mark first meeting as 'Tentative'
    When I mark record *M_1 as Tentative in #ServiceConsoleView.PlannedActivitiesDashlet.ActivitiesList
    # Mark second meeting as 'Held'
    When I mark record *M_2 as Held in #ServiceConsoleView.PlannedActivitiesDashlet.ActivitiesList
    # Cancel
    When I Confirm confirmation alert
    # Mark third meeting (created by user) as 'Declined'
    When I close alert

    # Verify number of records displayed on the tab
    Then I verify the record count in Meetings tab is equal to 1 in #ServiceConsoleView.PlannedActivitiesDashlet

    # Navigate to Calls tab
    When I navigate to Calls tab in #ServiceConsoleView.PlannedActivitiesDashlet

    # Log call record as Admin user
    When I Log Call in #ServiceConsoleView.PlannedActivitiesDashlet
    When I provide input for #CallsDrawer.HeaderView view
      | *    | name       |
      | Ca_1 | First Call |
    When I provide input for #CallsDrawer.RecordView view
      | *    | duration                                       | description          | direction |
      | Ca_1 | 12/01/2025-02:00pm ~ 12/01/2025-03:00pm (1 hr) | Testing with Seedbed | Outbound  |
    When I click Save button on #CallsDrawer header
    When I close alert

    # Set Filter to 'Today'
    When I set filter as Today in #ServiceConsoleView.PlannedActivitiesDashlet

    # Verify number of records displayed on the tab
    Then I verify the record count in Calls tab is equal to 1 in #ServiceConsoleView.PlannedActivitiesDashlet

    # Verify that call record is present in the Calls tab of the dashlet
    Then I verify *TodayC record info in #ServiceConsoleView.PlannedActivitiesDashlet.ActivitiesList
      | fieldName | value        |
      | name      | Today's Call |
      | label     | Overdue      |

    # Set filter to 'Future'
    When I set filter as Future in #ServiceConsoleView.PlannedActivitiesDashlet

    # Verify number of records displayed on the tab
    Then I verify the record count in Calls tab is equal to 1 in #ServiceConsoleView.PlannedActivitiesDashlet

    # Verify that record is present in the dashlet
    Then I verify *Ca_1 record info in #ServiceConsoleView.PlannedActivitiesDashlet.ActivitiesList
      | fieldName | value      |
      | name      | First Call |

    # Mark call as Declined
    When I mark record *Ca_1 as Declined in #ServiceConsoleView.PlannedActivitiesDashlet.ActivitiesList


  @active_tasks_dashlet
  Scenario: Service Console > Overview Tab > Active Tasks dashlet

    # Create 'due now' tasks records
    Given 2 Tasks records exist:
      | *          | name           | status      | priority | date_start      | date_due | description             |
      | T{{index}} | Task {{index}} | Not Started | High     | now -{{index}}d | now      | Tasks to complete today |

    # Navigate to Service Console
    When I choose Home in modules menu and select "Service Console" menu item

    # Verify number of records displayed on the tab
    Then I verify the record count in Due Now tab is equal to 2 in #ServiceConsoleView.ActiveTasksDashlet

    # Verify record is present in 'Due Now' tab
    Then I verify *T1 record info in #ServiceConsoleView.ActiveTasksDashlet.ActivitiesList
      | fieldName | value   |
      | name      | Task 1  |
      | label     | Overdue |

    # Verify record is present in 'Due Now' tab
    Then I verify *T2 record info in #ServiceConsoleView.ActiveTasksDashlet.ActivitiesList
      | fieldName | value   |
      | name      | Task 2  |
      | label     | Overdue |

    # Complete task
    When I mark record *T1 as Completed in #ServiceConsoleView.ActiveTasksDashlet.ActivitiesList
    When I Confirm confirmation alert
    When I close alert

    # Verify number of records displayed on the tab
    Then I verify the record count in Due Now tab is equal to 1 in #ServiceConsoleView.ActiveTasksDashlet

    # Navigate to 'Upcoming' tab
    When I navigate to Upcoming tab in #ServiceConsoleView.ActiveTasksDashlet

    # Verify number of records on the tab
    Then I verify the record count in Upcoming tab is equal to 0 in #ServiceConsoleView.ActiveTasksDashlet

    # Create 'Upcoming' task record
    When I Create Task in #ServiceConsoleView.ActiveTasksDashlet
    When I click show more button on #TasksDrawer view
    When I provide input for #TasksDrawer.HeaderView view
      | *   | name              |
      | T_1 | Finalize the sale |
    When I provide input for #TasksDrawer.RecordView view
      | *   | date_start         | date_due   | status      | priority | description               |
      | T_1 | 05/01/2025-12:00pm | 05/10/2025 | Not Started | High     | Seedbed testing for Tasks |
    When I click Save button on #TasksDrawer header
    When I close alert

    # Verify number of records on the tab
    Then I verify the record count in Upcoming tab is equal to 1 in #ServiceConsoleView.ActiveTasksDashlet

    # Verify record is present in 'Upcoming' tab
    Then I verify *T_1 record info in #ServiceConsoleView.ActiveTasksDashlet.ActivitiesList
      | fieldName | value             |
      | name      | Finalize the sale |

    # Navigate to 'Upcoming' tab
    When I navigate to To Do tab in #ServiceConsoleView.ActiveTasksDashlet

    # Verify number of records on the tab
    Then I verify the record count in To Do tab is equal to 0 in #ServiceConsoleView.ActiveTasksDashlet

    # Create task record with no due date
    When I Create Task in #ServiceConsoleView.ActiveTasksDashlet
    When I click show more button on #TasksDrawer view
    When I provide input for #TasksDrawer.HeaderView view
      | *   | name        |
      | T_2 | Do it later |
    When I provide input for #TasksDrawer.RecordView view
      | *   | date_start         | status      | priority | description               |
      | T_2 | 05/01/2025-12:00pm | In Progress | High     | Seedbed testing for Tasks |
    When I click Save button on #TasksDrawer header
    When I close alert

    # Verify number of records on the tab
    Then I verify the record count in To Do tab is equal to 1 in #ServiceConsoleView.ActiveTasksDashlet

    When I navigate to Due Now tab in #ServiceConsoleView.ActiveTasksDashlet
    When I select *T2 in #ServiceConsoleView.ActiveTasksDashlet.ActivitiesList
    Then I should see #T2Record view


  @user_profile
  Scenario: User Profile > Change License type
    When I choose Profile in the user actions menu
    # Change value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Enterprise" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value            |
      | Sugar Enterprise |
    When I click on Cancel button on #UserProfile
