# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @job5 @pr
Feature: Sugar Sell Renewals Console Verification > Accounts Tab
  As a sales agent I need to be able to verify Accounts Tab functionality of Renewals Console

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
      | value                        |
      | Sugar Enterprise, Sugar Sell |
    When I click on Cancel button on #UserProfile


  @renewals_console @dashlets_verification
  Scenario: Renewal Console > Accounts Tab > Main

    # Create new non-admin user
    Given I create custom user "user"

    Given 5 Accounts records exist:
      | *           | name              | description           | annual_revenue | industry | account_type | assigned_user_id |
      | A_{{index}} | Account {{index}} | Account's Description | {{index}}0K    | Apparel  | Competitor   | 1                |

    # Change 'assigned to' field for one of the accounts
    And I perform mass update of Accounts [*A_5] with the following values:
      | fieldName          | value          |
      | assigned_user_name | user userLName |

    # Navigate to Renewal Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Overview tab in Renewal Console
    When I select Accounts tab in #RenewalsConsoleView

    # Verify that record is present in multiline list view
    Then I verify fields for *A_1 in #AccountsList.MultilineListView
      | fieldName      | value      |
      | name           | Account 1  |
      | industry       | Apparel    |
      | annual_revenue | 10K        |
      | account_type   | Competitor |


    # Verify that record is present in multiline list view
    Then I verify fields for *A_2 in #AccountsList.MultilineListView
      | fieldName      | value      |
      | name           | Account 2  |
      | industry       | Apparel    |
      | annual_revenue | 20K        |
      | account_type   | Competitor |

    # Verify that record is present in multiline list view
    Then I verify fields for *A_3 in #AccountsList.MultilineListView
      | fieldName      | value      |
      | name           | Account 3  |
      | industry       | Apparel    |
      | annual_revenue | 30K        |
      | account_type   | Competitor |


    # Verify that record is present in multiline list view
    Then I verify fields for *A_4 in #AccountsList.MultilineListView
      | fieldName      | value      |
      | name           | Account 4  |
      | industry       | Apparel    |
      | annual_revenue | 40K        |
      | account_type   | Competitor |


    # Accounts assigned to different user shouldn't be displayed in Renewal Console
    Then I should not see *A_5 in #AccountsList.MultilineListView

    # Click the record to open side drawer
    When I select *A_1 in #AccountsList.MultilineListView
    # Verify that account name is updated in the header of Dashable Record dashlet
    Then I verify fields on #RenewalsConsoleView.DashableRecordDashlet
      | fieldName | value     |
      | name      | Account 1 |

    # Select another account while side drawer is opened
    When I select *A_2 in #AccountsList.MultilineListView
    # Verify that accont name is updated in the header of Dashable Record dashlet
    Then I verify fields on #RenewalsConsoleView.DashableRecordDashlet
      | fieldName | value     |
      | name      | Account 2 |

    # Select another account while side drawer is opened
    When I select *A_3 in #AccountsList.MultilineListView
    # Verify that account name is updated in the header of Dashable Record dashlet
    Then I verify fields on #RenewalsConsoleView.DashableRecordDashlet
      | fieldName | value     |
      | name      | Account 3 |

    # Close side drawer
    When I close side drawer in #RenewalsConsoleView

    # Open selected record in new tab
    When I choose "Open in New Tab" action for *A_1 in #AccountsList.MultilineListView
    # Switch to a new tab
    And I switch to tab 1

    # Edit record and Save in a separate tab
    When I click show more button on #A_1Record view
    When I click Edit button on #A_1Record header
    When I provide input for #A_1Record.HeaderView view
      | name        |
      | Account 1.1 |
    When I provide input for #A_1Record.RecordView view
      | website          | industry | account_type | service_level | phone_office | phone_alternate |
      | www.sugarcrm.com | Banking  | Customer     | Tier 2        | 555-555-0000 | 555-555-0001    |
    When I click Save button on #A_1Record header
    When I close alert

    # Return to Renewal Console tab
    When I switch to tab 0

    # Refresh the browser
    When I refresh the browser

    # Verify that record is present in multiline list view
    Then I verify fields for *A_1 in #AccountsList.MultilineListView
      | fieldName | value       |
      | name      | Account 1.1 |

    # Click the record to open side drawer
    When I select *A_2 in #AccountsList.MultilineListView

    # Edit selected record in new tab
    When I choose "Edit in New Tab" action for *A_2 in #AccountsList.MultilineListView

    # Switch to a new tab
    And I switch to tab 2

    # Edit record and Save in a separate tab
    When I provide input for #A_2Record.HeaderView view
      | name        |
      | Account 2.2 |
    When I provide input for #A_2Record.RecordView view
      | website        |
      | www.google.com |
    When I click Save button on #A_2Record header
    When I close alert

    # Return to Renewal Console tab
    When I switch to tab 0

    # Refresh the browser
    When I refresh the browser

    # Verify that record is present in multiline list view
    Then I verify fields for *A_2 in #AccountsList.MultilineListView
      | fieldName | value       |
      | name      | Account 2.2 |


  @renewals-console @rc_dashable_record_dashlet
  Scenario: Renewal Console > Accounts Tab > Dashable Record dashlet > Cancel/Save

    # Create account record
    Given Accounts records exist:
      | *   | name      | website        | industry | account_type | service_level | phone_office | phone_alternate | email (primary) | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | A_1 | Account 1 | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com     | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |

    # Create Contact records related to the account
    Given 5 Contacts records exist related via contacts link to *A_1:
      | *            | first_name       | last_name       | email                                   |
      | Co_{{index}} | cFirst_{{index}} | cLast_{{index}} | contact_{{index}}@example.org (primary) |

    # Create Opportunity related to the account
    Given Opportunities records exist related via Opportunities link to *A_1:
      | *     | name          |
      | Opp_1 | Opportunity 1 |

    # Create Quote records related to the account
    Given 2 Quotes records exist related via quotes link to *A_1:
      | *           | name            | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed |
      | Q_{{index}} | Quote {{index}} | City 1               | Street address here    | 220051                     | CA                    | USA                     | 2020-10-19T19:20:22+00:00  |


    # Navigate to Renewal Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Accounts tab
    When I select Accounts tab in #RenewalsConsoleView

    # Click the record to open side panel
    When I select *A_1 in #AccountsList.MultilineListView

    # Edit record inside the dashlet and cancel
    When I click Edit button in #RenewalsConsoleView.DashableRecordDashlet
    When I provide input for #A_1Record.RecordView view
      | website          | industry     |
      | www.sugarcrm.com | Construction |
    # Close side drawer without saving the case record
    When I close side drawer in #RenewalsConsoleView
    # Verify that Alert appears
    When I Cancel confirmation alert
    # Cancel Editing
    When I click Cancel button in #RenewalsConsoleView.DashableRecordDashlet

    # Edit record inside the dashlet and cancel
    When I click Edit button in #RenewalsConsoleView.DashableRecordDashlet
    When I provide input for #A_1Record.RecordView view
      | website     | industry  | account_type |
      | www.cnn.com | Education | Integrator   |
    When I click Cancel button in #RenewalsConsoleView.DashableRecordDashlet

    # Verify the edited value are not saved
    Then I verify fields on #A_1Record.RecordView
      | fieldName    | value   |
      | industry     | Apparel |
      | account_type | Analyst |

    # Edit record inside the dashlet and save
    When I click Edit button in #RenewalsConsoleView.DashableRecordDashlet
    When I provide input for #A_1Record.RecordView view
      | website     | industry  | account_type |
      | www.cnn.com | Education | Integrator   |
    When I click Save button in #RenewalsConsoleView.DashableRecordDashlet
    When I close alert

    # Verify the edited value is successfully saved
    Then I verify fields on #A_1Record.RecordView
      | fieldName    | value      |
      | industry     | Education  |
      | account_type | Integrator |

    # Switch to Contacts tab inside the Dashable Record dashlet
    When I switch to Contacts tab in #RenewalsConsoleView.DashableRecordDashlet
    # Verify contacts records related to the account appear in Contacts tab of Dashable Record dashlet
    Then I verify number of records in #RenewalsConsoleView.DashableRecordDashlet.ListView is 5
    And I should see [*Co_1, *Co_2, *Co_3, *Co_4, *Co_5] on #RenewalsConsoleView.DashableRecordDashlet.ListView dashlet

    # Switch to Opportunities tab inside the Dashable Record dashlet
    When I switch to Opportunities tab in #RenewalsConsoleView.DashableRecordDashlet
    # Verify opportunity records related to the account appear in Opportunities tab of Dashable Record dashlet
    Then I verify number of records in #RenewalsConsoleView.DashableRecordDashlet.ListView is 1
    And I should see [*Opp_1] on #RenewalsConsoleView.DashableRecordDashlet.ListView dashlet

    # Switch to Quotes tab inside the Dashable Record dashlet
    When I switch to Quotes tab in #RenewalsConsoleView.DashableRecordDashlet
    # Verify quote records related to the account appear in Quotes tab of Dashable Record dashlet
    Then I verify number of records in #RenewalsConsoleView.DashableRecordDashlet.ListView is 2
    And I should see [*Q_1, *Q_2] on #RenewalsConsoleView.DashableRecordDashlet.ListView dashlet

    # Click item from the Quotes tab
    When I select *Q_1 in #RenewalsConsoleView.DashableRecordDashlet.ListView
    Then I should see #Q_1Record view


  @renewals-console @rc_comment_log_dashlet
  Scenario: Renewals Console > Accounts Tab > Comment Log Dashlet > Add/Read Comment(s)
    # Create required Case and Account records
    And Accounts records exist:
      | *   | name      | assigned_user_id |
      | A_1 | Account_1 | 1                |
      | A_2 | Account_2 | 1                |

    # Create new non-admin user
    Given I create custom user "user"

    # Navigate to Service Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Cases tab
    When I select Accounts tab in #RenewalsConsoleView

    # Click the record to open side panel
    When I select *A_1 in #AccountsList.MultilineListView

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


  @renewals-console @rc_accounts_interactions_dashlet
  Scenario: Renewals Console > Accounts Tab > Account Interactions dashlet
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |

    And Contacts records exist:
      | *     | first_name | last_name | email                      | title               |
      | Con_1 | Contact1   | Contact1  | Co_1@example.net (primary) | Automation Engineer |

    # Navigate to Service Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Cases tab
    When I select Accounts tab in #RenewalsConsoleView

    # Click the record to open side panel
    When I select *A_1 in #AccountsList.MultilineListView

    # Create Call Record with status Held
    When I Log Call in #RenewalsConsoleView.AccountsInteractionsDashlet
    When I provide input for #CallsDrawer.HeaderView view
      | *    | name        | status |
      | Co_1 | Call (Held) | Held   |
    When I provide input for #CallsDrawer.RecordView view
      | *    | duration                                       | description          | direction |
      | Co_1 | 12/01/2020-02:00pm ~ 12/01/2020-03:00pm (1 hr) | Testing with Seedbed | Outbound  |
    When I click Save button on #CallsDrawer header
    When I close alert

    # Create Call Record with status Cancelled
    When I Log Call in #RenewalsConsoleView.AccountsInteractionsDashlet
    When I provide input for #CallsDrawer.HeaderView view
      | *    | name            | status   |
      | Co_2 | Call (Canceled) | Canceled |
    When I provide input for #CallsDrawer.RecordView view
      | *    | duration                                       | description          | direction |
      | Co_2 | 12/01/2020-02:00pm ~ 12/01/2020-03:00pm (1 hr) | Testing with Seedbed | Inbound   |
    When I click Save button on #CallsDrawer header
    When I close alert

    # Expand record in the dashlet
    When I expand record *Co_1 in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList

    # Verify record info in the expanded record info block
    Then I verify *Co_1 record info in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList
      | fieldName   | value                                 |
      | name        | Call (Held)                           |
      | status      | Held                                  |
      | duration    | 12/01/2020 02:00pm - 03:00pm (1 hour) |
      | direction   | Outbound                              |
      | description | Testing with Seedbed                  |

    # Expand another record in the dashlet
    When I expand record *Co_2 in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList

    # Verify record info in the expanded record info block
    Then I verify *Co_2 record info in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList
      | fieldName   | value                                 |
      | name        | Call (Canceled)                       |
      | status      | Canceled                              |
      | duration    | 12/01/2020 02:00pm - 03:00pm (1 hour) |
      | direction   | Inbound                               |
      | description | Testing with Seedbed                  |

    # Collapse expanded info block
    When I collapse record *Co_2 in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList

    # Schedule meeting with status 'Held'
    When I Schedule Meeting in #RenewalsConsoleView.AccountsInteractionsDashlet
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name      | status |
      | M_1 | Meeting 1 | Held   |
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | duration                                       | description          |
      | M_1 | 12/01/2020-05:00pm ~ 12/01/2020-06:00pm (1 hr) | Testing with Seedbed |
    When I click Save button on #MeetingsDrawer header
    When I close alert

    # Schedule meeting with status 'Canceled'
    When I Schedule Meeting in #RenewalsConsoleView.AccountsInteractionsDashlet
    When I provide input for #MeetingsDrawer.HeaderView view
      | *   | name      | status   |
      | M_2 | Meeting 2 | Canceled |
    When I provide input for #MeetingsDrawer.RecordView view
      | *   | duration                                       | description          |
      | M_2 | 12/01/2020-05:00pm ~ 12/01/2020-06:00pm (1 hr) | Testing with Seedbed |
    When I click Save button on #MeetingsDrawer header
    When I close alert

    # Expand record in the dashlet
    When I expand record *M_1 in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList

    # Verify record info in the expanded record info block
    Then I verify *M_1 record info in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList
      | fieldName   | value                                 |
      | name        | Meeting 1                             |
      | status      | Held                                  |
      | duration    | 12/01/2020 05:00pm - 06:00pm (1 hour) |
      | description | Testing with Seedbed                  |

    # Expand another record in the dashlet
    When I expand record *M_2 in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList

    # Verify record info in the expanded record info block
    Then I verify *M_2 record info in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList
      | fieldName   | value                                 |
      | name        | Meeting 2                             |
      | status      | Canceled                              |
      | duration    | 12/01/2020 05:00pm - 06:00pm (1 hour) |
      | description | Testing with Seedbed                  |

    # Collapse expanded info block
    When I collapse record *M_2 in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList

    # Create note record
    When I Create Note or Attachment in #RenewalsConsoleView.AccountsInteractionsDashlet
    When I provide input for #NotesDrawer.HeaderView view
      | *   | name   |
      | N_1 | Note 1 |
    When I provide input for #NotesDrawer.RecordView view
      | *   | description        | contact_name      |
      | N_1 | Note 1 description | Contact1 Contact1 |
    When I click Save button on #NotesDrawer header
    When I close alert

    # Expand another record in the dashlet
    When I expand record *N_1 in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList

    # Verify record info in the expanded record info block
    Then I verify *N_1 record info in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList
      | fieldName   | value              |
      | subject     | Note 1             |
      | contact     | Contact1 Contact1  |
      | description | Note 1 description |

    # Collapse expanded record info block
    When I collapse record *N_1 in #RenewalsConsoleView.AccountsInteractionsDashlet.InteractionsList


  @renewals-console @rc_accounts_active_subscriptions
  Scenario: Renewals Console > Accounts Tab > Active Subscriptions dashlet
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
    And Opportunities records exist related via opportunities link to *A_1:
      | *name |
      | Opp_1 |

    # Navigate to Renewal Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Accounts tab
    When I select Accounts tab in #RenewalsConsoleView

    # Click the record to open side panel
    When I select *A_1 in #AccountsList.MultilineListView

    # Verify that dashlet is empty (nothing to display)
    Then I verify 'No active subscriptions' message appears in #RenewalsConsoleView.ActiveSubscriptionsDashlet

    # Add RLI records
    Given RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | likely_case | sales_stage | quantity | service | service_duration_value | service_duration_unit | service_start_date |
      | RLI_1 | now         | 1000        | Closed Won  | 1        | true    | 2                      | year                  | 2019-11-06         |
      | RLI_2 | now         | 2000        | Closed Won  | 1        | true    | 19                     | month                 | 2019-11-06         |
      | RLI_3 | now         | 3000        | Closed Won  | 1        | true    | 17                     | month                 | 2019-11-06         |
      | RLI_4 | now         | 4000        | Closed Won  | 1        | true    | 1                      | year                  | 2019-11-06         |
      | RLI_5 | now         | 5000        | Closed Won  | 1        | true    | 90                     | day                   | 2019-11-06         |

    # Mass Update service_start_date to today
    When I perform mass update of all RevenueLineItems with the following values:
      | fieldName          | value |
      | service_start_date | today |

    # Navigate to Renewal Console
    When I choose Home in modules menu

    # Select Accounts tab
    When I select Accounts tab in #RenewalsConsoleView

    # Click the record to open side panel
    When I select *A_1 in #AccountsList.MultilineListView

    # Verify Active Subscriptions
    Then I verify that dashboard2by2_bottom_left element from #RenewalsConsoleView still looks like ActiveSubscriptions_1


  @renewals-console @rc_date_of_next_renewal
  Scenario: Renewal Console > Accounts Tab > Date of Next Renewal

    # Create account record
    Given Accounts records exist:
      | *   | name      | website        | industry | account_type | service_level | phone_office | phone_alternate | email (primary) | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | A_1 | Account 1 | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com     | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |

    # Create Opportunity related to the account
    Given Opportunities records exist related via Opportunities link to *A_1:
      | *     | name          |
      | Opp_1 | Opportunity 1 |

    # Add RLI records
    Given RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | likely_case | sales_stage         | quantity | product_type      | service | service_duration_value | service_duration_unit | service_start_date | renewable |
      | RLI_1 | now +11d    | 1000        | Prospecting         | 1        | Existing Business | true    | 2                      | year                  | 2019-11-06         | true      |
      | RLI_2 | now +12d    | 2000        | Qualification       | 1        | New Business      | true    | 19                     | month                 | 2019-11-06         | true      |
      | RLI_3 | now +13d    | 3000        | Needs Analysis      | 1        | Existing Business | true    | 17                     | month                 | 2019-11-06         | true      |
      | RLI_4 | now +14d    | 4000        | Value Proposition   | 1        | New Business      | true    | 1                      | year                  | 2019-11-06         | false     |
      | RLI_5 | now +15d    | 5000        | Id. Decision Makers | 1        |                   | true    | 90                     | day                   | 2019-11-06         | true      |
      | RLI_6 | now +16d    | 1000        | Closed Won          | 1        | Existing Business | true    | 2                      | year                  | 2019-11-06         | true      |
      | RLI_7 | now +17d    | 1000        | Closed Lost         | 1        | Existing Business | true    | 2                      | year                  | 2019-11-06         | true      |

    # Trigger sugar logic by mass-update opportunity name
    When I perform mass update of all RevenueLineItems with the following values:
      | fieldName        | value         |
      | opportunity_name | Opportunity 1 |

    # Navigate to Renewal Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Accounts tab
    When I select Accounts tab in #RenewalsConsoleView

    # Verify that next renewal date is properly calculated
    Then I verify fields for *A_1 in #AccountsList.MultilineListView
      | fieldName         | value      |
      | name              | Account 1  |
      | industry          | Apparel    |
      | annual_revenue    | 5000000    |
      | service_level     | Tier 1     |
      | account_type      | Analyst    |

  # There is an issue with using relative date:
  # if the test runs before noon of the current day (meaning today) in calculates next renewal date "in 12 days"
  # and after noon it calculates the the same date as "in 11 days"
  #      | next_renewal_date | in 12 days |

    # Delete one of the RLIs linked to opportunity
    When I delete *RLI_1 record in RevenueLineItems list view

    # Navigate to Renewal Console
    When I choose Home in modules menu

    # Select Accounts tab
    When I select Accounts tab in #RenewalsConsoleView

    # Verify that Next Renewal Date field is properly updated
#    Then I verify fields for *A_1 in #AccountsList.MultilineListView
#      | fieldName         | value      |
#      | next_renewal_date | in 14 days |




  @user_profile
  Scenario: User Profile > Change license type
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Sell" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value            |
      | Sugar Enterprise |
    When I click on Cancel button on #UserProfile


