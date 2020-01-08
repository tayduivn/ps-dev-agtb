# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules @job6
Feature: Sugar Mega Menu > Quick Create

  Background:
    Given I am logged in

  @quick_create_account
  Scenario Outline: Sugar Mega Menu > Quick Create > Create Account

    When I choose Accounts in the Quick Create actions menu
    When I click show more button on #AccountsDrawer view

    When I provide input for #AccountsDrawer.HeaderView view
      | *   | name   |
      | A_1 | <name> |
    When I provide input for #AccountsDrawer.RecordView view
      | *   | website   | industry   | account_type   | service_level   | phone_office   | phone_alternate   | email   | phone_fax   | twitter   | description   | sic_code   | ticker_symbol   | annual_revenue   | employees   | ownership   | rating   | billing_address_city   | billing_address_street   | billing_address_postalcode   | billing_address_state   | billing_address_country   |
      | A_1 | <website> | <industry> | <account_type> | <service_level> | <phone_office> | <phone_alternate> | <email> | <phone_fax> | <twitter> | <description> | <sic_code> | <ticker_symbol> | <annual_revenue> | <employees> | <ownership> | <rating> | <billing_address_city> | <billing_address_street> | <billing_address_postalcode> | <billing_address_state> | <billing_address_country> |
    When I click Save button on #AccountsDrawer header
    When I close alert
    When I choose Accounts in modules menu
    Then I should see *A_1 in #AccountsList.ListView
    When I select *A_1 in #AccountsList.ListView
    Then I should see #A_1Record view
    Then I click show more button on #A_1Record view

    # Verify that record is successfully created
    Then I verify fields on #A_1Record
      | fieldName                  | value                        |
      | name                       | <name>                       |
      | website                    | <http://><website>           |
      | industry                   | <industry>                   |
      | account_type               | <account_type>               |
      | service_level              | <service_level>              |
      | phone_office               | <phone_office>               |
      | phone_alternate            | <phone_alternate>            |
      | email                      | <email>                      |
      | phone_fax                  | <phone_fax>                  |
      | twitter                    | <twitter>                    |
      | description                | <description>                |
      | sic_code                   | <sic_code>                   |
      | ticker_symbol              | <ticker_symbol>              |
      | annual_revenue             | <annual_revenue>             |
      | employees                  | <employees>                  |
      | ownership                  | <ownership>                  |
      | rating                     | <rating>                     |
      | billing_address_city       | <billing_address_city>       |
      | billing_address_street     | <billing_address_street>     |
      | billing_address_postalcode | <billing_address_postalcode> |
      | billing_address_state      | <billing_address_state>      |
      | billing_address_country    | <billing_address_country>    |

    Examples:
      | name      | http:// | website        | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | http:// | www.google.com | Apparel  | Analyst      | Tier 1        | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |


  @quick_create_contact
  Scenario: Sugar Mega Menu > Quick Create > Create Contact
    When I choose Contacts in the Quick Create actions menu
    When I click show more button on #ContactsDrawer view

    When I provide input for #ContactsDrawer.HeaderView view
      | *   | first_name | last_name |
      | C_1 | Alex       | Nisevich  |
    When I provide input for #ContactsDrawer.RecordView view
      | *   | department | phone_mobile | email             | primary_address_city | primary_address_street | primary_address_postalcode | primary_address_state | primary_address_country | title               |
      | C_1 | Products   | 408-233-3456 | alex1@example.org | City 1               | Street address here    | 220051                     | WA                    | USA                     | Automation Engineer |

    When I click Save button on #ContactsDrawer header
    When I close alert
    When I choose Contacts in modules menu
    Then I should see *C_1 in #ContactsList.ListView
    When I select *C_1 in #ContactsList.ListView
    Then I should see #C_1Record view
    Then I click show more button on #C_1Record view

    # Verify that record is successfully created
    Then I verify fields on #C_1Record
      | fieldName                  | value               |
      | name                       | Alex Nisevich       |
      | department                 | Products            |
      | phone_mobile               | 408-233-3456        |
      | primary_address_city       | City 1              |
      | primary_address_street     | Street address here |
      | primary_address_postalcode | 220051              |
      | primary_address_state      | WA                  |
      | primary_address_country    | USA                 |
      | title                      | Automation Engineer |


  @quick_create_opportunity @pr
  Scenario: Sugar Mega Menu > Quick Create > Opportunity
    Given Accounts records exist:
      | *name | website        | industry | account_type | service_level | phone_office | phone_alternate |
      | Acc_1 | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    |

    When I choose Opportunities in the Quick Create actions menu
    When I click show more button on #OpportunitiesDrawer view

    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name               |
      | Opp_1 | My New Opportunity |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | account_name |
      | Opp_1 | Acc_1        |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | *name | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI1  | 12/12/2022  | 15000     | Qualification | 5        | 10000       |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Verify that record is successfully created
    When I choose Opportunities in modules menu
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Preview view
    Then I verify fields on #Opp_1Preview.PreviewView
      | fieldName    | value              |
      | name         | My New Opportunity |
      | best_case    | $15,000.00         |
      | amount       | $10,000.00         |
      | worst_case   | $10,000.00         |
      | date_closed  | 12/12/2022         |
      | sales_status | In Progress        |


  @quick_create_create_lead
  Scenario: Sugar Mega Menu > Quick Create > Lead
    When I choose Leads in the Quick Create actions menu
    When I click show more button on #LeadsDrawer view
    When I provide input for #LeadsDrawer.HeaderView view
      | *   | salutation | first_name | last_name |
      | L_1 | Mr.        | Novak      | Djokovic  |
    When I provide input for #LeadsDrawer.RecordView view
      | *   | title                              | phone_mobile | website               | account_name    |
      | L_1 | Serbian professional tennis player | 888-233-3221 | novakdjokovic.com/en/ | Novak's Account |
    When I click Save button on #LeadsDrawer header
    When I close alert

    # Verify that record is successfully created
    When I choose Leads in modules menu
    When I click on preview button on *L_1 in #LeadsList.ListView
    Then I should see #L_1Preview view
    Then I verify fields on #L_1Preview.PreviewView
      | fieldName    | value                              |
      | name         | Mr. Novak Djokovic                 |
      | account_name | Novak's Account                    |
      | website      | http://novakdjokovic.com/en/       |
      | phone_mobile | 888-233-3221                       |
      | title        | Serbian professional tennis player |


  @quick_create_log_call
  Scenario: Sugar Mega Menu > Quick Create > Log Call
    Given Accounts records exist:
      | *name | website        | industry | account_type | service_level | phone_office | phone_alternate |
      | Acc_1 | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    |

    When I choose Calls in the Quick Create actions menu
    When I click show more button on #CallsDrawer view
    When I provide input for #CallsDrawer.HeaderView view
      | *    | name          |
      | Ca_1 | Customer Call |
    When I provide input for #CallsDrawer.RecordView view
      | *    | duration                                       | description          | parent_name   | direction |
      | Ca_1 | 12/01/2020-02:00pm ~ 12/01/2020-03:00pm (1 hr) | Testing with Seedbed | Account,Acc_1 | Outbound  |
    When I click Save button on #CallsDrawer header
    When I close alert

    # Verify that record is successfully created
    When I choose Calls in modules menu
    When I click on preview button on *Ca_1 in #CallsList.ListView
    Then I should see #Ca_1Preview view
    Then I verify fields on #Ca_1Preview.PreviewView
      | fieldName   | value                                 |
      | name        | Customer Call                         |
      | direction   | Outbound                              |
      | description | Testing with Seedbed                  |
      | status      | Scheduled                             |
      | duration    | 12/01/2020 02:00pm - 03:00pm (1 hour) |


  @quick_create_schedule_meeting
  Scenario: Sugar Mega Menu > Quick Create > Schedule Meeting
    Given Accounts records exist:
      | *name | website        | industry | account_type | service_level | phone_office | phone_alternate |
      | Acc_1 | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    |

    When I choose Meetings in the Quick Create actions menu
    When I click show more button on #MeetingsDrawer view
    When I provide input for #MeetingsDrawer.HeaderView view
      | *    | name                 |
      | Me_1 | Meeting with Clients |
    When I provide input for #MeetingsDrawer.RecordView view
      | *    | duration                                       | description          | parent_name   |
      | Me_1 | 12/01/2020-02:00pm ~ 12/01/2020-03:00pm (1 hr) | Testing with Seedbed | Account,Acc_1 |
    When I click Save button on #MeetingsDrawer header
    When I close alert

    # Verify that record is successfully created
    When I choose Meetings in modules menu
    When I click on preview button on *Me_1 in #MeetingsList.ListView
    Then I should see #Me_1Preview view
    Then I verify fields on #Me_1Preview.PreviewView
      | fieldName   | value                                 |
      | name        | Meeting with Clients                  |
      | description | Testing with Seedbed                  |
      | status      | Scheduled                             |
      | duration    | 12/01/2020 02:00pm - 03:00pm (1 hour) |


  @quick_create_task
  Scenario: Sugar Mega Menu > Quick Create > Create Task
    Given Accounts records exist:
      | *name | website        | industry | account_type | service_level | phone_office | phone_alternate |
      | Acc_1 | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    |

    When I choose Tasks in the Quick Create actions menu
    When I click show more button on #TasksDrawer view
    When I provide input for #TasksDrawer.HeaderView view
      | *    | name     |
      | Ta_1 | New Task |
    When I provide input for #TasksDrawer.RecordView view
      | *    | date_start         | date_due   | status      | priority | description               |
      | Ta_1 | 05/01/2020-12:00pm | 05/10/2020 | Not Started | High     | Seedbed testing for Tasks |
    When I click Save button on #TasksDrawer header
    When I close alert

    # Verify that record is successfully created
    When I choose Tasks in modules menu
    When I click on preview button on *Ta_1 in #TasksList.ListView
    Then I should see #Ta_1Preview view
    Then I verify fields on #Ta_1Preview.PreviewView
      | fieldName   | value                     |
      | name        | New Task                  |
      | status      | Not Started               |
      | priority    | High                      |
      | date_start  | 05/01/2020 12:00pm        |
      | description | Seedbed testing for Tasks |


  @quick_create_task
  Scenario: Sugar Mega Menu > Quick Create > Create Task
    When I choose Tasks in the Quick Create actions menu
    When I click show more button on #TasksDrawer view
    When I provide input for #TasksDrawer.HeaderView view
      | *    | name     |
      | Ta_1 | New Task |
    When I provide input for #TasksDrawer.RecordView view
      | *    | date_start         | date_due   | status      | priority | description               |
      | Ta_1 | 05/01/2020-12:00pm | 05/10/2020 | Not Started | High     | Seedbed testing for Tasks |
    When I click Save button on #TasksDrawer header
    When I close alert

    # Verify that record is successfully created
    When I choose Tasks in modules menu
    When I click on preview button on *Ta_1 in #TasksList.ListView
    Then I should see #Ta_1Preview view
    Then I verify fields on #Ta_1Preview.PreviewView
      | fieldName   | value                     |
      | name        | New Task                  |
      | status      | Not Started               |
      | priority    | High                      |
      | date_start  | 05/01/2020 12:00pm        |
      | description | Seedbed testing for Tasks |


  @quick_create_note_or_attachment
  Scenario: Sugar Mega Menu > Quick Create > Create Note or Attachment
    Given Accounts records exist:
      | *name | website        | industry | account_type | service_level | phone_office | phone_alternate |
      | Acc_1 | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    |

    When I choose Notes in the Quick Create actions menu
    When I click show more button on #NotesDrawer view
    When I provide input for #NotesDrawer.HeaderView view
      | *   | name  |
      | N_1 | Note1 |

    # Populate record data
    When I provide input for #NotesDrawer.RecordView view
      | *   | description       | portal_flag | tag   | parent_name   |
      | N_1 | Note1 description | True        | note1 | Account,Acc_1 |
    # Save
    When I click Save button on #NotesDrawer header
    When I close alert

    # Verify that record is created successfully
    Then Notes *N_1 should have the following values:
      | fieldName   | value             |
      | name        | Note1             |
      | portal_flag | true              |
      | description | Note1 description |
      | parent_name | Acc_1             |

  @quick_create_revenue_line_item
  Scenario: Sugar Mega Menu > Quick Create > Create Revenue Line Item
    Given Opportunities records exist:
      | name  |
      | Opp_1 |
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |

    When I choose RevenueLineItems in the Quick Create actions menu
    When I click show more button on #RevenueLineItemsDrawer view
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | *     | name           |
      | RLI_1 | New RLI record |
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | *     | date_closed | likely_case | opportunity_name | sales_stage    | quantity |
      | RLI_1 | 11/20/2018  | 20,000.00   | Opp_1            | Needs Analysis | 5.5      |
    # Save
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert

    # Verify that record is created successfully
    Then RevenueLineItems *RLI_1 should have the following values:
      | fieldName        | value          |
      | name             | New RLI record |
      | date_closed      | 11/20/2018     |
      | likely_case      | $20,000.00     |
      | opportunity_name | Opp_1          |
      | account_name     | Acc_1          |
      | sales_stage      | Needs Analysis |
      | probability      | 25             |
      | quantity         | 5.50           |
      | discount_price   | $20,000.00     |
      | total_amount     | $110,000.00    |
