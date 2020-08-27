# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @job3 @pr @ent-only
Feature: Sugar Sell Renewals Console Verification > Opportunities Tab
  As a sales agent I need to be able to verify Opportunities Tab functionality of Renewals Console

  Background:
    Given I am logged in


  @user_profile
  Scenario: User Profile > Change license type
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Sell" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value      |
      | Sugar Sell |
    When I click on Cancel button on #UserProfile


  @renewals_console @dashlets_verification
  Scenario: Renewal Console > Opportunities Tab > Main

    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
    And Opportunities records exist related via opportunities link to *A_1:
      | *name | assigned_user_id |
      | Opp_1 | 1                |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | worst_case | likely_case | best_case | sales_stage | quantity | service | service_duration_value | service_duration_unit |
      | RLI_1 | now         | 1000       | 2000        | 3000      | Prospecting | 1        | true    | 2                      | year                  |

    Given Opportunities records exist related via opportunities link to *A_1:
      | *name | assigned_user_id |
      | Opp_2 | 1                |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_2:
      | *name | date_closed | likely_case | sales_stage | quantity | service | service_duration_value | service_duration_unit |
      | RLI_1 | now         | 1000        | Closed Won  | 1        | true    | 2                      | year                  |

    Given Opportunities records exist related via opportunities link to *A_1:
      | *name | assigned_user_id |
      | Opp_3 | 1                |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_3:
      | *name | date_closed | likely_case | sales_stage | quantity | service | service_duration_value | service_duration_unit |
      | RLI_1 | now         | 1000        | Closed Lost | 1        | true    | 2                      | year                  |

    Given Opportunities records exist related via opportunities link to *A_1:
      | *name | assigned_user_id |
      | Opp_4 | 1                |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_4:
      | *name | date_closed | worst_case | likely_case | best_case | sales_stage        | quantity | service | service_duration_value | service_duration_unit |
      | RLI_1 | now         | 13000      | 15000       | 17000     | Negotiation/Review | 1        | true    | 2                      | year                  |

    # Link opportunities to the account to re-enforce the calculations
    When I perform mass update of all Opportunities with the following values:
      | fieldName    | value     |
      | account_name | Account_1 |

    # Navigate to Renewal Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Opportunities tab in Renewal Console
    When I select Opportunities tab in #RenewalsConsoleView

    # Verify that record is present in multiline list view
    Then I verify fields for *Opp_1 in #OpportunitiesList.MultilineListView
      | fieldName | value |
      | name      | Opp_1 |

    # Verify that record is present in multiline list view
    Then I verify fields for *Opp_4 in #OpportunitiesList.MultilineListView
      | fieldName | value |
      | name      | Opp_4 |

    # CLosed opportunities should not be present in multiline list view
    Then I should not see *Opp_2 in #OpportunitiesList.MultilineListView
    Then I should not see *Opp_3 in #OpportunitiesList.MultilineListView

    # Click the record to open side drawer
    When I select *Opp_1 in #OpportunitiesList.MultilineListView
    # Verify that opportunity name is updated in the header of Dashable Record dashlet
    Then I verify fields on #RenewalsConsoleView.DashableRecordDashlet
      | fieldName | value |
      | name      | Opp_1 |

    # Select another record while side drawer is opened
    When I select *Opp_4 in #OpportunitiesList.MultilineListView
    # Verify that account name is updated in the header of Dashable Record dashlet
    Then I verify fields on #RenewalsConsoleView.DashableRecordDashlet
      | fieldName | value |
      | name      | Opp_4 |

    # Close side drawer
    When I close side drawer in #RenewalsConsoleView

    # Open selected record in new tab
    When I choose "Open in New Tab" action for *Opp_1 in #OpportunitiesList.MultilineListView
    # Switch to a new tab
    And I switch to tab 1

    # Edit record and Save in a separate tab
    When I click show more button on #Opp_1Record view
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.HeaderView view
      | name                |
      | Great Opportunity ! |
    When I provide input for #Opp_1Record.RecordView view
      | opportunity_type  | next_step         | lead_source |
      | Existing Business | Discuss the Price | Conference  |
    When I click Save button on #A_1Record header
    When I close alert

    # Return to Renewal Console tab
    When I switch to tab 0

    # Refresh the browser
    When I refresh the browser

    # Verify that record is present in multiline list view
    Then I verify fields for *Opp_1 in #OpportunitiesList.MultilineListView
      | fieldName | value               |
      | name      | Great Opportunity ! |

    # Click the record to open side drawer
    When I select *Opp_4 in #OpportunitiesList.MultilineListView

    # Edit selected record in new tab
    When I choose "Edit in New Tab" action for *Opp_4 in #OpportunitiesList.MultilineListView

    # Switch to a new tab
    And I switch to tab 2

    # Edit record and Save in a separate tab
    When I provide input for #Opp_4Record.HeaderView view
      | name                |
      | Super Opportunity ! |
    When I provide input for #Opp_4Record.RecordView view
      | opportunity_type | next_step            | lead_source    |
      | New Business     | Discuss the delivery | Self Generated |
    When I click Save button on #Opp_4Record header
    When I close alert

    # Return to Renewal Console tab
    When I choose Home in modules menu

    # Refresh the browser
    When I refresh the browser

    # Verify that record is present in multiline list view
    Then I verify fields for *Opp_4 in #OpportunitiesList.MultilineListView
      | fieldName | value               |
      | name      | Super Opportunity ! |


  @renewals-console @rc_dashable_record_dashlet
  Scenario: Renewal Console > Opportunities Tab > Dashable Record dashlet > Cancel/Save
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
    And Opportunities records exist related via opportunities link to *A_1:
      | *name | assigned_user_id |
      | Opp_1 | 1                |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | worst_case | likely_case | best_case | sales_stage | quantity | service | service_start_date | service_duration_value | service_duration_unit |
      | RLI_1 | now         | 1000       | 2000        | 3000      | Prospecting | 1        | true    | now + 10d          | 2                      | year                  |

    # Create Quote records related to the account
    Given Quotes records exist related via quotes link to *Opp_1:
      | *   | name    | date_quote_expected_closed | quote_stage |
      | Q_1 | Quote 1 | 2020-10-19T19:20:22+00:00  | Negotiation |
      | Q_2 | Quote 2 | 2020-10-19T19:20:22+00:00  | Delivered   |
    
    # Create Contact records related to the account
    Given 5 Contacts records exist related via contacts link to *Opp_1:
      | *            | first_name       | last_name       | email                                   |
      | Co_{{index}} | cFirst_{{index}} | cLast_{{index}} | contact_{{index}}@example.org (primary) |

    # Link opportunities to the account to re-enforce the calculations
    When I perform mass update of all Opportunities with the following values:
      | fieldName    | value     |
      | account_name | Account_1 |

    # Navigate to Renewal Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Opportunities tab in Renewal Console
    When I select Opportunities tab in #RenewalsConsoleView

    # Click the record to open side panel
    When I select *Opp_1 in #AccountsList.MultilineListView

    # Edit record inside the dashlet and cancel
    When I click Edit button in #RenewalsConsoleView.DashableRecordDashlet
    When I click show more button on #Opp_1Record view
    When I provide input for #Opp_1Record.RecordView view
      | opportunity_type  |
      | Existing Business |
    When I close side drawer in #RenewalsConsoleView
    # Verify that Alert appears
    When I Cancel confirmation alert
    # Cancel Editing
    When I click Cancel button in #RenewalsConsoleView.DashableRecordDashlet

    # Edit record inside the dashlet and cancel
    When I click Edit button in #RenewalsConsoleView.DashableRecordDashlet
    When I provide input for #Opp_1Record.RecordView view
      | opportunity_type  |
      | Existing Business |
    When I click Cancel button in #RenewalsConsoleView.DashableRecordDashlet

    # Verify the edited value are not saved
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName    | value |
      | amount       | $2,000.00   |
      | sales_status | In Progress |
      | best_case    | $3,000.00   |

    # Edit record inside the dashlet and save
    When I click Edit button in #RenewalsConsoleView.DashableRecordDashlet
    When I provide input for #Opp_1Record.RecordView view
      | sales_stage   | date_closed | service_start_date |
      | Qualification | now + 1d    | now +30d           |
    When I click Save button in #RenewalsConsoleView.DashableRecordDashlet
    When I close alert

    # Verify the edited values are successfully saved in Opportunities record view dashlet
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName          | value         |
      | sales_stage        | Qualification |
      | date_closed        | now + 1d      |
      | service_start_date | now + 30d     |

    # Switch to Contacts tab inside the Dashable Record dashlet
    When I switch to Contacts tab in #RenewalsConsoleView.DashableRecordDashlet
    # Verify contacts records related to the opportunity appear in Contacts tab of Dashable Record dashlet
    Then I verify number of records in #RenewalsConsoleView.DashableRecordDashlet.ListView is 5
    And I should see [*Co_1, *Co_2, *Co_3, *Co_4, *Co_5] on #RenewalsConsoleView.DashableRecordDashlet.ListView dashlet

    # Switch to Quotes tab inside the Dashable Record dashlet
    When I switch to Quotes tab in #RenewalsConsoleView.DashableRecordDashlet
    # Verify Quotes records related to the opportunity appear in Quotes tab of Dashable Record dashlet
    Then I verify number of records in #RenewalsConsoleView.DashableRecordDashlet.ListView is 2
    And I should see [*Q_1, *Q_2] on #RenewalsConsoleView.DashableRecordDashlet.ListView dashlet

    # Switch to Revenue Line Items tab inside the Dashable Record dashlet
    When I switch to Revenue Line Items tab in #RenewalsConsoleView.DashableRecordDashlet
    # Verify RLI records related to the opportunity appear in RLI tab of Dashable Record dashlet
    Then I verify number of records in #RenewalsConsoleView.DashableRecordDashlet.ListView is 1
    And I should see [*RLI_1] on #RenewalsConsoleView.DashableRecordDashlet.ListView dashlet

    # Click item from the RLI tab
    When I select *RLI_1 in #RenewalsConsoleView.DashableRecordDashlet.ListView
    Then I should see #RLI_1Record view
    When I click show more button on #RLI_1Record view

    # Verify the edited value is successfully saved
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName          | value         |
      | sales_stage        | Qualification |
      | date_closed        | now + 1d      |
      | service_start_date | now + 30d     |


  @renewals-console @rc_comment_log_dashlet
  Scenario: Renewals Console > Opportunities Tab > Comment Log Dashlet > Add/Read Comment(s)
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
      | A_2 | Account_2 |
    And Opportunities records exist related via opportunities link to *A_1:
      | *name | assigned_user_id |
      | Opp_1 | 1                |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | worst_case | likely_case | best_case | sales_stage | quantity | service | service_duration_value | service_duration_unit |
      | RLI_1 | now         | 1000       | 2000        | 3000      | Prospecting | 1        | true    | 2                      | year                  |

    # Create new non-admin user
    Given I create custom user "user"

    # Navigate to Renewals Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Cases tab
    When I select Opportunities tab in #RenewalsConsoleView

    # Click the record to open side panel
    When I select *Opp_1 in #OpportunitiesList.MultilineListView

    When I add the following comment into #RenewalsConsoleView.CommentLogDashlet:
      | value                |
      | My first new comment |

    When I add the following comment into #RenewalsConsoleView.CommentLogDashlet:
      | value                 |
      | My second new comment |

    When I add the following comment into #RenewalsConsoleView.CommentLogDashlet:
      | value                      |
      | Add reference to the @user |

    When I add the following comment into #RenewalsConsoleView.CommentLogDashlet:
      | value                           |
      | Add reference to the #Account_2 |

    Then I verify comments in #RenewalsConsoleView.CommentLogDashlet
      | comment                             |
      | Add reference to the Account_2      |
      | Add reference to the user userLName |
      | My second new comment               |
      | My first new comment                |


  @renewals-console @rc_dashable_record_dashlet
  Scenario: Renewal Console > Opportunities Tab > Account Dashable Record dashlet > Cancel/Save
    Given Accounts records exist:
      | *   | name      | website                 |
      | A_1 | Account_1 | http://www.cnn.com      |
      | A_2 | Account_2 | http://www.sugarcrm.com |
    And Opportunities records exist related via opportunities link to *A_1:
      | *name | assigned_user_id |
      | Opp_1 | 1                |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | worst_case | likely_case | best_case | sales_stage | quantity | service | service_duration_value | service_duration_unit |
      | RLI_1 | now         | 1000       | 2000        | 3000      | Prospecting | 1        | true    | 2                      | year                  |

    # Link opportunities to the account to re-enforce the calculations
    When I perform mass update of all Opportunities with the following values:
      | fieldName    | value     |
      | account_name | Account_1 |

    # Navigate to Renewals Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Opportunities tab in Renewal Console
    When I select Opportunities tab in #RenewalsConsoleView

    # Click the record to open side panel
    When I select *Opp_1 in #AccountsList.MultilineListView

    # Edit record inside the dashlet and cancel
    When I click Edit button in #RenewalsConsoleView.AccountInfoDashlet
    When I provide input for #A_1Record.RecordView view
      | website              | industry  | parent_name | account_type | service_level | phone_office |
      | http://www.yahoo.com | Chemicals | Account_2   | Competitor   | Tier 1        | 408.233.3221 |
    When I close side drawer in #RenewalsConsoleView
    # Verify that Alert appears
    When I Cancel confirmation alert
    # Cancel Editing
    When I click Cancel button in #RenewalsConsoleView.AccountInfoDashlet

    # Edit record inside the dashlet and cancel
    When I click Edit button in #RenewalsConsoleView.AccountInfoDashlet
    When I provide input for #A_1Record.RecordView view
      | website              | industry  | parent_name | account_type | service_level | phone_office |
      | http://www.yahoo.com | Chemicals | Account_2   | Competitor   | Tier 1        | 408.233.3221 |
    When I click Cancel button in #RenewalsConsoleView.AccountInfoDashlet

    # Verify the edited value are not saved
    Then I verify fields on #A_1Record.RecordView
      | fieldName | value              |
      | website   | http://www.cnn.com |

    # Edit record inside the dashlet and save
    When I click Edit button in #RenewalsConsoleView.AccountInfoDashlet
    When I click show more button in #ServiceConsoleView.AccountInfoDashlet
    When I provide input for #A_1Record.RecordView view
      | website              | industry  | parent_name | account_type | service_level | phone_office | description         | annual_revenue |
      | http://www.yahoo.com | Chemicals | Account_2   | Competitor   | Tier 1        | 408.233.3221 | Account Description | $100,000.00    |
    When I click Save button in #RenewalsConsoleView.AccountInfoDashlet
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
    When I click show less button in #ServiceConsoleView.AccountInfoDashlet


  @renewals-console @rc_opp_interactions_dashlet
  Scenario: Renewals Console > Opportunities Tab > Opportunities Interactions dashlet
    Given Accounts records exist:
      | *   | name      | website            |
      | A_1 | Account_1 | http://www.cnn.com |
    And Opportunities records exist related via opportunities link to *A_1:
      | *name | assigned_user_id |
      | Opp_1 | 1                |

    And Contacts records exist:
      | *     | first_name | last_name | email                      | title               |
      | Con_1 | Contact1   | Contact1  | Co_1@example.net (primary) | Automation Engineer |

    # Navigate to Renewals Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Cases tab
    When I select Opportunities tab in #RenewalsConsoleView

    # Click the record to open side panel
    When I select *Opp_1 in #OpportunitiesList.MultilineListView

    # Create Call Record with status Held
    When I Log Call in #RenewalsConsoleView.OpportunityInteractionsDashlet
    When I provide input for #CallsDrawer.HeaderView view
      | *    | name        | status |
      | Co_1 | Call (Held) | Held   |
    When I provide input for #CallsDrawer.RecordView view
      | *    | duration                                       | description          | direction |
      | Co_1 | 12/01/2020-02:00pm ~ 12/01/2020-03:00pm (1 hr) | Testing with Seedbed | Outbound  |
    When I click Save button on #CallsDrawer header
    When I close alert

    # Create Call Record with status Cancelled
    When I Log Call in #RenewalsConsoleView.OpportunityInteractionsDashlet
    When I provide input for #CallsDrawer.HeaderView view
      | *    | name            | status   |
      | Co_2 | Call (Canceled) | Canceled |
    When I provide input for #CallsDrawer.RecordView view
      | *    | duration                                       | description          | direction |
      | Co_2 | 12/01/2020-02:00pm ~ 12/01/2020-03:00pm (1 hr) | Testing with Seedbed | Inbound   |
    When I click Save button on #CallsDrawer header
    When I close alert

    # Expand record in the dashlet
    When I expand record *Co_1 in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList

    # Verify record info in the expanded record info block
    Then I verify *Co_1 record info in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList
      | fieldName   | value                                 |
      | name        | Call (Held)                           |
      | status      | Held                                  |
      | duration    | 12/01/2020 02:00pm - 03:00pm (1 hour) |
      | direction   | Outbound                              |
      | description | Testing with Seedbed                  |

    # Expand another record in the dashlet
    When I expand record *Co_2 in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList

    # Verify record info in the expanded record info block
    Then I verify *Co_2 record info in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList
      | fieldName   | value                                 |
      | name        | Call (Canceled)                       |
      | status      | Canceled                              |
      | duration    | 12/01/2020 02:00pm - 03:00pm (1 hour) |
      | direction   | Inbound                               |
      | description | Testing with Seedbed                  |

    # Collapse expanded info block
    When I collapse record *Co_2 in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList

    # Schedule meeting with status 'Held'
    When I Schedule Meeting in #RenewalsConsoleView.OpportunityInteractionsDashlet
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name      | status |
      | M_1 | Meeting 1 | Held   |
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | duration                                       | description          |
      | M_1 | 12/01/2020-05:00pm ~ 12/01/2020-06:00pm (1 hr) | Testing with Seedbed |
    When I click Save button on #MeetingsDrawer header
    When I close alert

    # Schedule meeting with status 'Canceled'
    When I Schedule Meeting in #RenewalsConsoleView.OpportunityInteractionsDashlet
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name      | status   |
      | M_2 | Meeting 2 | Canceled |
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | duration                                       | description          |
      | M_2 | 12/01/2020-05:00pm ~ 12/01/2020-06:00pm (1 hr) | Testing with Seedbed |
    When I click Save button on #MeetingsDrawer header
    When I close alert

    # Expand record in the dashlet
    When I expand record *M_1 in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList

    # Verify record info in the expanded record info block
    Then I verify *M_1 record info in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList
      | fieldName   | value                                 |
      | name        | Meeting 1                             |
      | status      | Held                                  |
      | duration    | 12/01/2020 05:00pm - 06:00pm (1 hour) |
      | description | Testing with Seedbed                  |

    # Expand another record in the dashlet
    When I expand record *M_2 in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList

    # Verify record info in the expanded record info block
    Then I verify *M_2 record info in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList
      | fieldName   | value                                 |
      | name        | Meeting 2                             |
      | status      | Canceled                              |
      | duration    | 12/01/2020 05:00pm - 06:00pm (1 hour) |
      | description | Testing with Seedbed                  |

    # Collapse expanded info block
    When I collapse record *M_2 in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList

    # Create note record
    When I Create Note or Attachment in #RenewalsConsoleView.OpportunityInteractionsDashlet
    When I provide input for #NotesDrawer.HeaderView view
      | *   | name   |
      | N_1 | Note 1 |
    When I provide input for #NotesDrawer.RecordView view
      | *   | description        | contact_name      |
      | N_1 | Note 1 description | Contact1 Contact1 |
    When I click Save button on #NotesDrawer header
    When I close alert

    # Expand another record in the dashlet
    When I expand record *N_1 in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList

    # Verify record info in the expanded record info block
    Then I verify *N_1 record info in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList
      | fieldName   | value              |
      | subject     | Note 1             |
      | contact     | Contact1 Contact1  |
      | description | Note 1 description |

    # Collapse expanded record info block
    When I collapse record *N_1 in #RenewalsConsoleView.OpportunityInteractionsDashlet.InteractionsList


  @renewals-console @rc_opportunities_config
  Scenario: Renewals Console > Console Settings > Opportunities Tab
    # Create an account record
    Given Accounts records exist:
      | *   | name      | industry  | annual_revenue | service_level | type    | assigned_user_id |
      | A_1 | Account_1 | Chemicals | 30K            | T3            | Analyst | 1                |

    # Create an Opportunity related to the account
    Given Opportunities records exist related via Opportunities link to *A_1:
      | *     | name          | my_favorite | lead_source | assigned_user_id | next_step      |
      | Opp_1 | Opportunity 1 | true        | Employee    | 1                | Needs Analysis |

    # Add RLI record related to the above opportunity
    Given RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | likely_case | sales_stage    | quantity |
      | RLI_1 | now +11d    | 1000        | Needs Analysis | 1        |

    # Create an Opportunity related to the account
    Given Opportunities records exist related via Opportunities link to *A_1:
      | *     | name          | my_favorite | lead_source | assigned_user_id | next_step   |
      | Opp_2 | Opportunity 2 | false       | Cold Call   | 1                | Prospecting |

    # Add RLI record related to the above opportunity
    Given RevenueLineItems records exist related via revenuelineitems link to *Opp_2:
      | *name | date_closed | likely_case | sales_stage | quantity |
      | RLI_2 | now +15d    | 1000        | Prospecting | 1        |

    # Create an Opportunity related to the account
    Given Opportunities records exist related via Opportunities link to *A_1:
      | *     | name          | my_favorite | lead_source | assigned_user_id | next_step           |
      | Opp_3 | Opportunity 3 | true        | Conference  | 1                | Perception Analysis |

    # Add RLI record related to the above opportunity
    Given RevenueLineItems records exist related via revenuelineitems link to *Opp_3:
      | *name | date_closed | likely_case | sales_stage         | quantity |
      | RLI_3 | now +13d    | 1000        | Perception Analysis | 1        |

    # Create an Opportunity related to the account
    Given Opportunities records exist related via Opportunities link to *A_1:
      | *     | name          | my_favorite | lead_source | assigned_user_id | next_step   |
      | Opp_4 | Opportunity 4 | false       | Trade Show  | 1                | Prospecting |

    # Add RLI record related to the above opportunity
    Given RevenueLineItems records exist related via revenuelineitems link to *Opp_4:
      | *name | date_closed | likely_case | sales_stage | quantity |
      | RLI_4 | now +10d    | 1000        | Prospecting | 1        |

    # Create an Opportunity related to the account
    Given Opportunities records exist related via Opportunities link to *A_1:
      | *     | name          | my_favorite | lead_source | assigned_user_id | next_step          |
      | Opp_5 | Opportunity 5 | true        | Direct Mail | 1                | Negotiation/Review |

    # Add RLI record related to the above opportunity
    Given RevenueLineItems records exist related via revenuelineitems link to *Opp_5:
      | *name | date_closed | likely_case | sales_stage        | quantity |
      | RLI_5 | now +12d    | 1000        | Negotiation/Review | 1        |

    # Trigger sugar logic by mass-update opportunities 'sales_stage' field
    When I perform mass update of all RevenueLineItems with the following values:
      | fieldName          | value         |
      | assigned_user_name | Administrator |

    # Navigate to Renewals Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Opportunities tab in Renewals Console
    When I select Opportunities tab in #RenewalsConsoleView

    # Verify the order of records in the multiline list view after sorting order is changed
    Then I verify records order in #OpportunitiesList.MultilineListView
      | record_identifier | expected_list_order |
      | Opp_4             | 1                   |
      | Opp_1             | 2                   |
      | Opp_5             | 3                   |
      | Opp_3             | 4                   |
      | Opp_2             | 5                   |

    # Set sorting order in the Console Settings > Opportunities tab and save
    When I set sort order in Opportunities tab of #ConsoleSettingsConfig view:
      | sortOrderField | sortBy           | sortDirection |
      | primary        | Next Step        | Ascending     |
      | secondary      | Opportunity Name | Ascending     |

    # Verify the order of records in the multiline list view after sorting order is changed
    Then I verify records order in #OpportunitiesList.MultilineListView
      | record_identifier | expected_list_order |
      | Opp_1             | 1                   |
      | Opp_5             | 2                   |
      | Opp_3             | 3                   |
      | Opp_2             | 4                   |
      | Opp_4             | 5                   |

    # Set sorting order in the Console Settings > Opportunities tab and save
    When I set sort order in Opportunities tab of #ConsoleSettingsConfig view:
      | sortOrderField | sortBy      | sortDirection |
      | primary        | Lead Source | Ascending     |

    # Verify the order of records in the multiline list view after sorting order is changed
    Then I verify records order in #OpportunitiesList.MultilineListView
      | record_identifier | expected_list_order |
      | Opp_2             | 1                   |
      | Opp_3             | 2                   |
      | Opp_5             | 3                   |
      | Opp_1             | 4                   |
      | Opp_4             | 5                   |

    # Set filter in the Console Settings > Opportunities tab and save
    When I set the "My Favorites" filter in Opportunities tab of #ConsoleSettingsConfig view

    # Verify the order of records in the multiline list view after filter is applied
    Then I should not see *Opp_2 in #OpportunitiesList.MultilineListView
    Then I should not see *Opp_4 in #OpportunitiesList.MultilineListView

    Then I verify records order in #OpportunitiesList.MultilineListView
      | record_identifier | expected_list_order |
      | Opp_3             | 1                   |
      | Opp_5             | 2                   |
      | Opp_1             | 3                   |

    # Change sales stage of one of the RLI records to Closed Won
    When I perform mass update of RevenueLineItems [*RLI_3] with the following values:
      | fieldName   | value      |
      | sales_stage | Closed Won |

    # Navigate to Renewals Console
    When I choose Home in modules menu

    # Select Opportunities tab in Renewals Console
    When I select Opportunities tab in #RenewalsConsoleView

    # Verify the order of records in the multiline list view after filter is applied
    Then I should not see *Opp_3 in #OpportunitiesList.MultilineListView
    Then I verify records order in #OpportunitiesList.MultilineListView
      | record_identifier | expected_list_order |
      | Opp_5             | 1                   |
      | Opp_1             | 2                   |

    # Restore default sorting order in the Console Settings > Opportunities tab and save
    When I restore defaults in Opportunities tab of #ConsoleSettingsConfig view

    # Verify the records in the multiline list view after sorting order is changed
    Then I verify records order in #AccountsList.MultilineListView
      | record_identifier | expected_list_order |
      | Opp_4             | 1                   |
      | Opp_1             | 2                   |
      | Opp_5             | 3                   |
      | Opp_2             | 4                   |


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

