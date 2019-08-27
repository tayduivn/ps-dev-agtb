# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @job4 @pr
Feature: Customer Service Console Verification
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
      | value                         |
      | Sugar Enterprise, Sugar Serve |
    When I click on Cancel button on #UserProfile


  @service_console
  Scenario: Service Console > Overview Tab > My Open Cases by Follow up date

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

    # Select Cases tab in Service Console
    Then I verify that first_row_left_dashlet element from #Dashboard.DashboardView still looks like myOpenCasesByFollowUpDate


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
    When I Schedule Meeting in #Dashboard.CsPlannedActivitiesDashlet
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name               |
      | M_1 | Initial discussion |
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | duration                                       | description          | invitees     |
      | M_1 | 12/01/2025-05:00pm ~ 12/01/2025-06:00pm (1 hr) | Testing with Seedbed | add: *user_1 |

    When I click Save button on #MeetingsDrawer header
    When I close alert

    # Schedule meeting 'Meeting 2' as Admin
    When I Schedule Meeting in #Dashboard.CsPlannedActivitiesDashlet
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name              |
      | M_2 | Follow Up meeting |
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | duration                                       | description          | invitees     |
      | M_2 | 12/15/2025-02:00pm ~ 12/15/2025-03:00pm (1 hr) | Testing with Seedbed | add: *user_1 |
    When I click Save button on #MeetingsDrawer header
    When I close alert

    # Navigate to meetings tab of Planned Activities Dashlet
    When I navigate to Meetings tab in #Dashboard.CsPlannedActivitiesDashlet

    # Set Filter to 'today'
    When I set filter as Today in #Dashboard.CsPlannedActivitiesDashlet

    # Verify number of records displayed on the tab
    Then I verify the record count in Meetings tab is equal to 1 in #Dashboard.CsPlannedActivitiesDashlet

    # Verify that meeting record is present in the meetings tab of the dashlet
    Then I verify *TodayM record info in #Dashboard.CsPlannedActivitiesDashlet.ActivitiesList
      | fieldName | value           |
      | name      | Today's Meeting |
      | label     | Overdue         |

    # Set Filter to future
    When I set filter as Future in #Dashboard.CsPlannedActivitiesDashlet

    # Set visibility to 'user'
    When I set visibility as 'user' in #Dashboard.CsPlannedActivitiesDashlet

    # Verify number of records displayed on the tab
    Then I verify the record count in Meetings tab is equal to 2 in #Dashboard.CsPlannedActivitiesDashlet

    # Verify that meeting record is present in the meetings tab of the dashlet
    Then I verify *M_1 record info in #Dashboard.CsPlannedActivitiesDashlet.ActivitiesList
      | fieldName | value              |
      | name      | Initial discussion |

    # Verify that meeting record is present in the meetings tab of the dashlet
    Then I verify *M_2 record info in #Dashboard.CsPlannedActivitiesDashlet.ActivitiesList
      | fieldName | value             |
      | name      | Follow Up meeting |

    # Set visibility to 'group'
    When I set visibility as 'group' in #Dashboard.CsPlannedActivitiesDashlet
    # Verify number of records displayed on the tab
    Then I verify the record count in Meetings tab is equal to 2 in #Dashboard.CsPlannedActivitiesDashlet

    # Verify that meeting record is present in the meetings tab of the dashlet
    Then I verify *M_1 record info in #Dashboard.CsPlannedActivitiesDashlet.ActivitiesList
      | fieldName | value              |
      | name      | Initial discussion |

    # Verify that meeting record is present in the meetings tab of the dashlet
    Then I verify *M_2 record info in #Dashboard.CsPlannedActivitiesDashlet.ActivitiesList
      | fieldName | value             |
      | name      | Follow Up meeting |

    # Mark first meeting as 'Tentative'
    When I mark record *M_1 as Tentative in #Dashboard.CsPlannedActivitiesDashlet.ActivitiesList
    # Mark second meeting as 'Held'
    When I mark record *M_2 as Held in #Dashboard.CsPlannedActivitiesDashlet.ActivitiesList
    # Cancel
    When I Confirm confirmation alert
    # Mark third meeting (created by user) as 'Declined'
    When I close alert

    # Verify number of records displayed on the tab
    Then I verify the record count in Meetings tab is equal to 1 in #Dashboard.CsPlannedActivitiesDashlet


    # Waiting for CS--357 to be fixed
#    # Navigate to Calls tab
#    When I navigate to Calls tab in #Dashboard.CsPlannedActivitiesDashlet
#
#    # Log call record as Admin user
#    When I Log Call in #Dashboard.CsPlannedActivitiesDashlet
#    When I provide input for #CallsDrawer.HeaderView view
#      | *    | name       |
#      | Ca_1 | First Call |
#    When I provide input for #CallsDrawer.RecordView view
#      | *    | duration                                       | description          | direction |
#      | Ca_1 | 12/01/2025-02:00pm ~ 12/01/2025-03:00pm (1 hr) | Testing with Seedbed | Outbound  |
#    When I click Save button on #CallsDrawer header
#    When I close alert
#
#    # Set Filter to 'Today'
#    When I set filter as Today in #Dashboard.CsPlannedActivitiesDashlet
#
#    # Verify number of records displayed on the tab
#    Then I verify the record count in Calls tab is equal to 1 in #Dashboard.CsPlannedActivitiesDashlet
#
#    # Verify that call record is present in the Calls tab of the dashlet
#    Then I verify *TodayC record info in #Dashboard.CsPlannedActivitiesDashlet.ActivitiesList
#      | fieldName | value        |
#      | name      | Today's Call |
#      | label     | Overdue      |
#
#    # Set filter to 'Future'
#    When I set filter as Future in #Dashboard.CsPlannedActivitiesDashlet
#
#    # Verify number of records displayed on the tab
#    Then I verify the record count in Calls tab is equal to 1 in #Dashboard.CsPlannedActivitiesDashlet
#
#    # Verify that record is present in the dashlet
#    Then I verify *Ca_1 record info in #Dashboard.CsPlannedActivitiesDashlet.ActivitiesList
#      | fieldName | value      |
#      | name      | First Call |
#
#    # Mark call as Declined
#    When I mark record *Ca_1 as Declined in #Dashboard.CsPlannedActivitiesDashlet.ActivitiesList


  @user_profile
  Scenario: User Profile > Change License type
    When I choose Profile in the user actions menu
    # Change value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Serve" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value            |
      | Sugar Enterprise |
    When I click on Cancel button on #UserProfile

