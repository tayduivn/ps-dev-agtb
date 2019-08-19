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
  Scenario: Service Console > Basic Verification

    # Create new non-admin user
    Given I create custom user "user"

    # Create required Case and Account records
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
      | A_2 | Account_2 |
    And Cases records exist related via cases link to *A_1:
      | *   | name   | source   | priority | status        | follow_up_datetime        | assigned_user_id |
      | C_1 | Case_1 | Internal | P1       | New           | 2025-11-20T22:27:00+00:00 | 1                |
      | C_2 | Case_2 | Forum    | P2       | Assigned      | 2025-10-20T22:27:00+00:00 | 1                |
      | C_3 | Case_3 | Web      | P3       | Pending Input | 2025-09-20T22:27:00+00:00 | 1                |
      | C_4 | Case_4 | Web      | P1       | Closed        | 2025-08-20T22:27:00+00:00 | 1                |
      | C_5 | Case_5 | Forum    | P2       | Assigned      | 2025-07-20T22:27:00+00:00 | 1                |

    # Change 'assigned to' field for one of the case
    And I perform mass update of Cases [*C_5] with the following values:
      | fieldName          | value          |
      | assigned_user_name | user userLName |

    # Navigate to Service Console
    When I choose Home in modules menu and select "Service Console" menu item

    # Select Cases tab in Service Console
    When I select Cases tab in #ServiceConsoleView

    # Check the list sorting order
    Then I verify the case records order in #CasesList.MultilineListView
      | record_identifier | expected_list_order |
      | C_3               | 1                   |
      | C_2               | 2                   |
      | C_1               | 3                   |

    # Verify that record is present in multiline list view
    Then I verify fields for *C_1 in #CasesList.MultilineListView
      | fieldName | value  |
      | name      | Case_1 |

    # Verify that record is present in multiline list view
    Then I verify fields for *C_2 in #CasesList.MultilineListView
      | fieldName | value  |
      | name      | Case_2 |

    # Verify that record is present in multiline list view
    Then I verify fields for *C_3 in #CasesList.MultilineListView
      | fieldName | value  |
      | name      | Case_3 |

    # Closed case and case assigned to different user shouldn't be displayed in Service Console
    Then I should not see *C_4 in #CasesList.MultilineListView
    Then I should not see *C_5 in #CasesList.MultilineListView

    # Click the record to open side drawer
    When I select *C_1 in #CasesList.MultilineListView
    # Verify that case name is updated in the header of Dashable Record dashlet
    Then I verify fields on #Dashboard.CsDashableRecordDashlet
      | fieldName | value  |
      | name      | Case_1 |

    # Select another case while side drawer is opened
    When I select *C_2 in #CasesList.MultilineListView
    # Verify that case name is updated in the header of Dashable Record dashlet
    Then I verify fields on #Dashboard.CsDashableRecordDashlet
      | fieldName | value  |
      | name      | Case_2 |

    # Select another case while side drawer is opened
    When I select *C_3 in #CasesList.MultilineListView
    # Verify that case name is updated in the header of Dashable Record dashlet
    Then I verify fields on #Dashboard.CsDashableRecordDashlet
      | fieldName | value  |
      | name      | Case_3 |

    # Close side drawer
    When I close side drawer in #ServiceConsoleView

    # Open selected record in new tab
    When I choose "Open in New Tab" action for *C_1 in #CasesList.MultilineListView
    # Switch to a new tab
    And I switch to tab 1

    # Edit record and Save in a separate tab
    When I click Edit button on #C_1Record header
    When I provide input for #CasesRecord.HeaderView view
      | name     |
      | Case_1.1 |
    When I provide input for #CasesRecord.RecordView view
      | account_name | priority | status   | type           | source   | description       | commentlog  |
      | Account_2    | Medium   | Assigned | Administration | Internal | Cases Description | Comment Log |
    When I click Save button on #C_1Record header
    When I close alert

    # Return to Service Console tab
    When I switch to tab 0

    # Refresh the browser
    When I refresh the browser

    # Verify that record is present in multiline list view
    Then I verify fields for *C_1 in #CasesList.MultilineListView
      | fieldName | value    |
      | name      | Case_1.1 |

    # Select Overview tab
    When I select Overview tab in #ServiceConsoleView
    Then I verify that first_row_left_dashlet element from #Dashboard.DashboardView still looks like sc_overview_toprow_left


  @service-console @cs_dashable_record_dashlet
  Scenario: Customer Service Console > Dashable Record dashlet > Cancel/Save
    # Create required Case and Account records
    Given Cases records exist:
      | *   | name   | source   | priority | status | assigned_user_id |
      | C_1 | Case_1 | Internal | P1       | New    | 1                |
    And Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |

    # Create Task records related to the case
    Given Tasks records exist related via tasks link to *C_1:
      | *  | name   | status        | priority | date_start          | date_due            | description   |
      | T1 | Task 1 | Not Started   | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Not Started   |
      | T2 | Task 2 | In Progress   | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | In Progress   |
      | T3 | Task 3 | Completed     | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Completed     |
      | T4 | Task 4 | Pending Input | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Pending Input |
      | T5 | Task 5 | Deferred      | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Deferred      |

    # Create Contact records related to the case
    Given 2 Contacts records exist related via contacts link to *C_1:
      | *            | first_name       | last_name       | email                                   |
      | Co_{{index}} | cFirst_{{index}} | cLast_{{index}} | contact_{{index}}@example.org (primary) |

    # Create Document records related to the case
    Given Documents records exist related via documents link to *C_1:
      | *   | name           |
      | D_1 | CS Document v1 |

    # Navigate to Service Console
    When I choose Home in modules menu and select "Service Console" menu item

    # Select Cases tab
    When I select Cases tab in #ServiceConsoleView

    # Click the record to open side panel
    When I select *C_1 in #CasesList.MultilineListView

    # Edit record inside the dashlet and cancel
    When I click Edit button in #Dashboard.CsDashableRecordDashlet
    When I provide input for #C_1Record.RecordView view
      | priority | account_name |
      | Medium   | Account_1    |
    # Close side drawer without saving the case record
    When I close side drawer in #ServiceConsoleView
    # Verify that Alert appears
    When I Cancel confirmation alert
    # Cancel Editing
    When I click Cancel button in #Dashboard.CsDashableRecordDashlet

    # Edit record inside the dashlet and cancel
    When I click Edit button in #Dashboard.CsDashableRecordDashlet
    When I provide input for #C_1Record.RecordView view
      | priority | account_name |
      | Medium   | Account_1    |
    When I click Cancel button in #Dashboard.CsDashableRecordDashlet

    # Verify the edited value are not saved
    Then I verify fields on #C_1Record.RecordView
      | fieldName    | value |
      | priority     | High  |
      | account_name |       |

    # Edit record inside the dashlet and save
    When I click Edit button in #Dashboard.CsDashableRecordDashlet
    When I provide input for #C_1Record.RecordView view
      | priority | account_name |
      | Medium   | Account_1    |
    When I click Save button in #Dashboard.CsDashableRecordDashlet
    When I close alert

    # Verify the edited value is successfully saved
    Then I verify fields on #C_1Record.RecordView
      | fieldName    | value     |
      | priority     | Medium    |
      | account_name | Account_1 |

    # Switch to Tasks tab inside the Dashable Record dashlet
    When I switch to Tasks tab in #Dashboard.CsDashableRecordDashlet
    # Verify task records related to the case appear in Tasks tab of Dashable Record dashlet
    Then I verify number of records in #Dashboard.CsDashableRecordDashlet.ListView is 5
    And I should see [*T1, *T2, *T3, *T4, *T5] on #Dashboard.CsDashableRecordDashlet.ListView dashlet

    # Switch to Contacts tab inside the Dashable Record dashlet
    When I switch to Contacts tab in #Dashboard.CsDashableRecordDashlet
    # Verify contact records related to the case appear in Contacts tab of Dashable Record dashlet
    Then I verify number of records in #Dashboard.CsDashableRecordDashlet.ListView is 2
    And I should see [*Co_1, *Co_2] on #Dashboard.CsDashableRecordDashlet.ListView dashlet

    # Switch to Documents tab inside the Dashable Record dashlet
    When I switch to Documents tab in #Dashboard.CsDashableRecordDashlet
    # Verify document records related to the case appear in Documents tab of Dashable Record dashlet
    Then I verify number of records in #Dashboard.CsDashableRecordDashlet.ListView is 1
    And I should see [*D_1] on #Dashboard.CsDashableRecordDashlet.ListView dashlet

    # Switch to Tasks tab
    When I switch to Tasks tab in #Dashboard.CsDashableRecordDashlet
    # Click item from the Tasks tab
    And I select *T1 in #Dashboard.CsDashableRecordDashlet.ListView
    Then I should see #T1Record view


  @service-console @cs_comment_log_dashlet
  Scenario: Customer Service Console > Comment Log Dashlet > Add/Read Comment(s)
    # Create required Case and Account records
    Given Cases records exist:
      | *   | name   | source   | priority | status | assigned_user_id |
      | C_1 | Case_1 | Internal | P1       | New    | 1                |
    And Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |

    # Create new non-admin user
    Given I create custom user "user"

    # Navigate to Service Console
    When I choose Home in modules menu and select "Service Console" menu item

    # Select Cases tab
    When I select Cases tab in #ServiceConsoleView

    # Click the record to open side panel
    When I select *C_1 in #CasesList.MultilineListView

    When I add the following comment into #Dashboard.CsCommentLogDashlet:
      | value                |
      | My first new comment |

    When I add the following comment into #Dashboard.CsCommentLogDashlet:
      | value                 |
      | My second new comment |

    When I add the following comment into #Dashboard.CsCommentLogDashlet:
      | value                      |
      | Add reference to the @user |

    When I add the following comment into #Dashboard.CsCommentLogDashlet:
      | value                           |
      | Add reference to the #Account_1 |

    Then I verify comments in #Dashboard.CsCommentLogDashlet
      | comment                             |
      | Add reference to the Account_1      |
      | Add reference to the user userLName |
      | My second new comment               |
      | My first new comment                |


  @service-console @cs_account_info_dashlet
  Scenario: Customer Service Console > Account Info > Cancel/Save
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
      | A_2 | Account_2 |
    And Cases records exist related via cases link to *A_1:
      | *   | name   | source   | priority | status | follow_up_datetime        | assigned_user_id |
      | C_1 | Case_1 | Internal | P1       | New    | 2020-11-20T22:27:00+00:00 | 1                |
    # Navigate to Service Console
    When I choose Home in modules menu and select "Service Console" menu item

    # Select Cases tab
    When I select Cases tab in #ServiceConsoleView

    # Click the record to open side panel
    When I select *C_1 in #CasesList.MultilineListView

    # Edit record inside the dashlet and cancel
    When I click Edit button in #Dashboard.CsAccountInfoDashlet
    When I provide input for #A_1Record.RecordView view
      | website              | industry  | parent_name | account_type | service_level | phone_office |
      | http://www.yahoo.com | Chemicals | Account_2   | Competitor   | Tier 1        | 408.233.3221 |
    # Close side drawer without saving the case record
    When I close side drawer in #ServiceConsoleView
    # Verify that Alert appears
    When I Cancel confirmation alert
    # Cancel Editing
    When I click Cancel button in #Dashboard.CsAccountInfoDashlet

    # Edit record inside the dashlet and save
    When I click Edit button in #Dashboard.CsAccountInfoDashlet
    When I click show more button in #Dashboard.CsAccountInfoDashlet
    When I provide input for #A_1Record.RecordView view
      | website              | industry  | parent_name | account_type | service_level | phone_office | description         | annual_revenue |
      | http://www.yahoo.com | Chemicals | Account_2   | Competitor   | Tier 1        | 408.233.3221 | Account Description | $100,000.00    |
    When I click Save button in #Dashboard.CsAccountInfoDashlet
    When I close alert

    # Verify the edited value is successfully saved
    Then I verify fields on #A_1Record.RecordView
      | fieldName      | value                |
      | website        | http://www.yahoo.com |
      | industry       | Chemicals            |
      | parent_name    | Account_2            |
      | account_type   | Competitor           |
      | service_level  | Tier 1               |
      | phone_office   | 408.233.3221         |
      | annual_revenue | $100,000.00          |
    When I click show less button in #Dashboard.CsAccountInfoDashlet


  @service-console @cs_cases_interactions_dashlet
  Scenario: Customer Service Console > Cases Interactions
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
    And Cases records exist related via cases link to *A_1:
      | *   | name   | source   | priority | status | follow_up_datetime        | assigned_user_id |
      | C_1 | Case_1 | Internal | P1       | New    | 2020-11-20T22:27:00+00:00 | 1                |

    And Contacts records exist:
      | *     | first_name | last_name | email                      | title               |
      | Con_1 | Contact1   | Contact1  | Co_1@example.net (primary) | Automation Engineer |

    # Navigate to Service Console
    When I choose Home in modules menu and select "Service Console" menu item

    # Select Cases tab
    When I select Cases tab in #ServiceConsoleView

    # Click the record to open side panel
    When I select *C_1 in #CasesList.MultilineListView

    # Create Call Record with status Held
    When I Log Call in #Dashboard.CsCasesInteractionsDashlet
    When I provide input for #CallsDrawer.HeaderView view
      | *    | name        | status |
      | Co_1 | Call (Held) | Held   |
    When I provide input for #CallsDrawer.RecordView view
      | *    | duration                                       | description          | direction |
      | Co_1 | 12/01/2020-02:00pm ~ 12/01/2020-03:00pm (1 hr) | Testing with Seedbed | Outbound  |
    When I click Save button on #CallsDrawer header
    When I close alert

    # Create Call Record with status Cancelled
    When I Log Call in #Dashboard.CsCasesInteractionsDashlet
    When I provide input for #CallsDrawer.HeaderView view
      | *    | name            | status   |
      | Co_2 | Call (Canceled) | Canceled |
    When I provide input for #CallsDrawer.RecordView view
      | *    | duration                                       | description          | direction |
      | Co_2 | 12/01/2020-02:00pm ~ 12/01/2020-03:00pm (1 hr) | Testing with Seedbed | Inbound   |
    When I click Save button on #CallsDrawer header
    When I close alert

    # Expand record in the dashlet
    When I expand record *Co_1 in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList

    # Verify record info in the expanded record info block
    Then I verify *Co_1 record info in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList
      | fieldName   | value                                 |
      | name        | Call (Held)                           |
      | status      | Held                                  |
      | duration    | 12/01/2020 02:00pm - 03:00pm (1 hour) |
      | direction   | Outbound                              |
      | description | Testing with Seedbed                  |

    # Expand another record in the dashlet
    When I expand record *Co_2 in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList

    # Verify record info in the expanded record info block
    Then I verify *Co_2 record info in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList
      | fieldName   | value                                 |
      | name        | Call (Canceled)                       |
      | status      | Canceled                              |
      | duration    | 12/01/2020 02:00pm - 03:00pm (1 hour) |
      | direction   | Inbound                               |
      | description | Testing with Seedbed                  |

    # Collapse expanded info block
    When I collapse record *Co_2 in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList

    # Schedule meeting with status 'Held'
    When I Schedule Meeting in #Dashboard.CsCasesInteractionsDashlet
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name      | status |
      | M_1 | Meeting 1 | Held   |
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | duration                                       | description          |
      | M_1 | 12/01/2020-05:00pm ~ 12/01/2020-06:00pm (1 hr) | Testing with Seedbed |
    When I click Save button on #MeetingsDrawer header
    When I close alert

    # Schedule meeting with status 'Canceled'
    When I Schedule Meeting in #Dashboard.CsCasesInteractionsDashlet
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name      | status   |
      | M_2 | Meeting 2 | Canceled |
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | duration                                       | description          |
      | M_2 | 12/01/2020-05:00pm ~ 12/01/2020-06:00pm (1 hr) | Testing with Seedbed |
    When I click Save button on #MeetingsDrawer header
    When I close alert

    # Expand record in the dashlet
    When I expand record *M_1 in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList

    # Verify record info in the expanded record info block
    Then I verify *M_1 record info in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList
      | fieldName   | value                                 |
      | name        | Meeting 1                             |
      | status      | Held                                  |
      | duration    | 12/01/2020 05:00pm - 06:00pm (1 hour) |
      | description | Testing with Seedbed                  |

    # Expand another record in the dashlet
    When I expand record *M_2 in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList

    # Verify record info in the expanded record info block
    Then I verify *M_2 record info in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList
      | fieldName   | value                                 |
      | name        | Meeting 2                             |
      | status      | Canceled                              |
      | duration    | 12/01/2020 05:00pm - 06:00pm (1 hour) |
      | description | Testing with Seedbed                  |

    # Collapse expanded info block
    When I collapse record *M_2 in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList

    # Create note record
    When I Create Note or Attachment in #Dashboard.CsCasesInteractionsDashlet
    When I provide input for #NotesDrawer.HeaderView view
      | *   | name   |
      | N_1 | Note 1 |
    When I provide input for #NotesDrawer.RecordView view
      | *   | description        | contact_name      |
      | N_1 | Note 1 description | Contact1 Contact1 |
    When I click Save button on #NotesDrawer header
    When I close alert

    # Expand another record in the dashlet
    When I expand record *N_1 in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList

    # Verify record info in the expanded record info block
    Then I verify *N_1 record info in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList
      | fieldName   | value              |
      | subject     | Note 1             |
      | contact     | Contact1 Contact1  |
      | description | Note 1 description |

    # Collapse expanded record info block
    When I collapse record *N_1 in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList


  @service-console @cs_dashable_record_dashlet @config
  Scenario: Customer Service Console > Dashable Record dashlet > Configuration Settings
    # Create required Case and Account records
    Given Accounts records exist:
      | *   | name      | website              | industry  | account_type |
      | A_1 | Account_1 | http://www.yahoo.com | Chemicals | Competitor   |

    And Cases records exist related via cases link to *A_1:
      | *   | name   | source   | priority | status | assigned_user_id |
      | C_1 | Case_1 | Internal | P1       | New    | 1                |

    # Create 7 notes records related to the case
    Given 7 Notes records exist related via notes link to *C_1:
      | *           | name                             |
      | N_{{index}} | Note {{index}} related to Case_1 |
    # Create 3 calls records related to the case
    And 3 Calls records exist related via calls link to *C_1:
      | *name          | assigned_user_id | date_start                | duration_hours | duration_minutes | direction | description      | status  |
      | Call_{{index}} | 1                | 2020-04-16T14:30:00-07:00 | 0              | 45               | Outbound  | Call to customer | Planned |
    # Create task records related to the case
    And Tasks records exist related via tasks link to *C_1:
      | *   | name   | status      | priority | date_start          | date_due            | description |
      | T_1 | Task 1 | Not Started | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Not Started |

    # Navigate to Service Console
    When I choose Home in modules menu and select "Service Console" menu item

    # Select Cases tab
    When I select Cases tab in #ServiceConsoleView

    # Click the record to open side panel
    When I select *C_1 in #CasesList.MultilineListView

    # Verify if all expected tabs are displayed in the dashlet
    Then I should see the following tabs in #Dashboard.CsDashableRecordDashlet dashlet:
      | tab_list  |
      | Cases     |
      | Tasks     |
      | Contacts  |
      | Documents |

    # Navigate to dashlet config screen
    When I edit #Dashboard.CsDashableRecordDashlet dashlet

    # Add modules as tabs to purposely exceed the 6 tabs limit
    When I add the following modules as tabs in #DashableRecordConfig view:
      | tab_list |
      | Calls    |
      | Notes    |
      | Account  |

    # Close Alert related to more than 6 modules selected
    When I close alert

    # Remove Accounts module to comply with 6 tab maximum limit
    When I remove the following modules as tabs in #DashableRecordConfig view:
      | tab_list |
      | Account  |

    # Navigate to Tasks tab and update values for specified fields in Tasks tab
    When I move to Tasks tab in #DashableRecordConfig view
    When I provide input for #TasksRecord.RecordView view
      | fields | auto_refresh     |
      | Status | Every 10 Minutes |

    # Navigate to Notes tab and update values for specified fields in Notes tab
    When I move to Notes tab in #DashableRecordConfig view
    When I provide input for #NotesRecord.RecordView view
      | limit | auto_refresh     |
      | 10    | Every 10 Minutes |

    # Save configuration changes
    When I click Save button on #DashableRecordConfig header
    When I close alert

    # Verify that 2 more tabs are added to the dashlet
    Then I should see the following tabs in #Dashboard.CsDashableRecordDashlet dashlet:
      | tab_list  |
      | Cases     |
      | Tasks     |
      | Contacts  |
      | Documents |
      | Calls     |
      | Notes     |

    # Navigate to newly added Calls tab inside the Dashable Record dashlet
    When I switch to Calls tab in #Dashboard.CsDashableRecordDashlet
    # Verify that calls records related to the case appear in the Calls tab of the dashlet
    Then I should see [*Call_1, *Call_2, *Call_3] on #Dashboard.CsDashableRecordDashlet.ListView dashlet

    # Navigate to Notes tab inside the Dashable Record dashlet
    When I switch to Notes tab in #Dashboard.CsDashableRecordDashlet
    # Verify that notes records related to the case appear in the Notes tab of the dashlet
    Then I should see [*N_1, *N_2, *N_3, *N_4, *N_5, *N_6, *N_7] on #Dashboard.CsDashableRecordDashlet.ListView dashlet

    # Navigate to Tasks tab inside the Dashable Record dashlet
    When I switch to Tasks tab in #Dashboard.CsDashableRecordDashlet
    # Verify that tasks records related to the case appear in the Tasks tab of the dashlet
    Then I should see [*T_1] on #Dashboard.CsDashableRecordDashlet.ListView dashlet

     # Getting value of specific field is not currently supported in List View dashlet
#    Then I verify field values for *T_1 in #Dashboard.CsDashableRecordDashlet.ListView
#      | fieldName | value       |
#      | name      | Task 1      |
#      | status    | Not Started |

    # Return back to dashlet config screen
    When I edit #Dashboard.CsDashableRecordDashlet dashlet

    # Remove previously added modules from the dashlet in configuration screen
    When I remove the following modules as tabs in #DashableRecordConfig view:
      | tab_list |
      | Calls    |
      | Notes    |

    # Remove previously added field(s) in Tasks tab
    When I remove the following fields from Tasks tab of #DashableRecordConfig view:
      | fields |
      | Status |

    # Save configuration changes
    When I click Save button on #DashableRecordConfig header
    When I close alert

    # Verify that 2 previously added tabs are successfully removed
    Then I should not see the following tabs in #Dashboard.CsDashableRecordDashlet dashlet:
      | tab_list  |
      | Calls     |
      | Notes     |


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
