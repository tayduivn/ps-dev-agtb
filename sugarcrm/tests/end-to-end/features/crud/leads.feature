# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_leads @job1
Feature: Leads module verification

  Background:
    Given I use default account
    Given I launch App

  @list @preview
  Scenario: Leads > List View > Preview
    Given Leads records exist:
      | *    | first_name | last_name | account_name   | title             | phone_mobile   | phone_work     | primary_address  | primary_address_city | primary_address_state | primary_address_postalcode | email            |
      | John | John       | Barlow    | John's Account | Software Engineer | (746) 079-5067 | (408) 536-6312 | 10050 N Wolfe Rd | Cupertino            | California            | 95014                      | John@example.org |
    Given I open about view and login
    When I choose Leads in modules menu
    Then I should see *John in #LeadsList.ListView
    Then I verify fields for *John in #LeadsList.ListView
      | fieldName | value       |
      | name      | John Barlow |
    When I click on preview button on *John in #LeadsList.ListView
    Then I should see #JohnPreview view
    Then I verify fields on #JohnPreview.PreviewView
      | fieldName | value             |
      | name      | John Barlow       |
      | title     | Software Engineer |
      | email     | John@example.org  |


  @list-search
  Scenario: Leads > List View > Filter > Search main input
    Given Leads records exist:
      | *      | first_name | last_name | account_name     | title             | email              |
      | Lead_1 | John       | Barlow    | John's Account   | Software Engineer | lead_1@example.net |
      | Lead_2 | Travis     | Hubbard   | Travis's Account | Software Engineer | lead_2@example.net |
      | Lead_3 | Alex       | Nisevich  | Alex's Account   | Quality Engineer  | lead_3@example.net |
    Given I open about view and login
    When I choose Leads in modules menu
    Then I should see *Lead_1 in #LeadsList.ListView
    Then I should see *Lead_2 in #LeadsList.ListView
    Then I should see *Lead_3 in #LeadsList.ListView
    When I search for "Travis" in #LeadsList.FilterView view
    Then I should not see *Lead_1 in #LeadsList.ListView
    Then I should see *Lead_2 in #LeadsList.ListView
    Then I should not see *Lead_3 in #LeadsList.ListView
    Then I verify fields for *Lead_2 in #LeadsList.ListView
      | fieldName    | value            |
      | name         | Travis Hubbard   |
      | account_name | Travis's Account |
      | status       | New              |

  @list-edit
  Scenario: Contracts > List View > Inline Edit
    Given Leads records exist:
      | *      | first_name | last_name | account_name   | title             | email                        |
      | Lead_1 | John       | Barlow    | John's Account | Software Engineer | lead_1@example.net (primary) |
    Given I open about view and login
    When I choose Leads in modules menu
    When I click on Edit button for *Lead_1 in #LeadsList.ListView
    When I set values for *Lead_1 in #LeadsList.ListView
      | fieldName    | value          |
      | first_name   | Alex           |
      | last_name    | Nisevich       |
      | account_name | Alex's Account |
    When I click on Cancel button for *Lead_1 in #LeadsList.ListView
    Then I verify fields for *Lead_1 in #LeadsList.ListView
      | fieldName    | value          |
      | name         | John Barlow    |
      | account_name | John's Account |
    When I click on Edit button for *Lead_1 in #LeadsList.ListView
    When I set values for *Lead_1 in #LeadsList.ListView
      | fieldName    | value          |
      | first_name   | Alex           |
      | last_name    | Nisevich       |
      | account_name | Alex's Account |
    When I click on Save button for *Lead_1 in #LeadsList.ListView
    Then I verify fields for *Lead_1 in #LeadsList.ListView
      | fieldName    | value          |
      | name         | Alex Nisevich  |
      | account_name | Alex's Account |

  @list-delete
  Scenario: Leads > List View > Delete
    Given Leads records exist:
      | *      | first_name | last_name | account_name    | title         | email                        |
      | Lead_1 | Andre      | Agassi    | Andre's Account | Tennis Player | lead_1@example.net (primary) |
    Given I open about view and login
    When I choose Leads in modules menu
    When I click on Delete button for *Lead_1 in #LeadsList.ListView
    When I Cancel confirmation alert
    Then I should see #LeadsList view
    Then I should see *Lead_1 in #LeadsList.ListView
    When I click on Delete button for *Lead_1 in #LeadsList.ListView
    When I Confirm confirmation alert
    Then I should see #LeadsList view
    Then I should not see *Lead_1 in #LeadsList.ListView

  @delete
  Scenario: Leads >  Record View > Delete
    Given Leads records exist:
      | *      | first_name | last_name | account_name   | title                      | email                        |
      | Lead_1 | Rafael     | Nadal     | Rafa's Account | Professional Tennis Player | lead_1@example.net (primary) |
    Given I open about view and login
    When I choose Leads in modules menu
    When I select *Lead_1 in #LeadsList.ListView
    Then I should see #Lead_1Record view
    When I open actions menu in #Lead_1Record
    * I choose Delete from actions menu in #Lead_1Record
    When I Cancel confirmation alert
    Then I should see #Lead_1Record view
    Then I verify fields on #Lead_1Record.HeaderView
      | fieldName | value        |
      | name      | Rafael Nadal |
    Then I verify fields on #Lead_1Record.RecordView
      | fieldName    | value          |
      | account_name | Rafa's Account |
    When I open actions menu in #Lead_1Record
    * I choose Delete from actions menu in #Lead_1Record
    When I Confirm confirmation alert
    Then I should see #LeadsList.ListView view
    Then I should not see *Lead_1 in #LeadsList.ListView

  @copy
  Scenario: Leads > Record view > Copy > Cancel
    Given Leads records exist:
      | *      | first_name | last_name | account_name   | title                      | email                        |
      | Lead_1 | Rafael     | Nadal     | Rafa's Account | Professional Tennis Player | lead_1@example.net (primary) |
    Given I open about view and login
    When I choose Leads in modules menu
    When I select *Lead_1 in #LeadsList.ListView
    Then I should see #Lead_1Record view
    When I open actions menu in #Lead_1Record
    # Canceling Part
    When I choose Copy from actions menu in #Lead_1Record
    When I provide input for #LeadsDrawer.HeaderView view
      | first_name | last_name |
      | Roger      | Federer   |
    When I provide input for #LeadsDrawer.RecordView view
      | account_name    |
      | Roger's Account |
    When I click Cancel button on #LeadsDrawer header
    Then I verify fields on #Lead_1Record.HeaderView
      | fieldName | value        |
      | name      | Rafael Nadal |
    Then I verify fields on #Lead_1Record.RecordView
      | fieldName    | value          |
      | account_name | Rafa's Account |
    # Saving Part
    When I open actions menu in #Lead_1Record
    When I choose Copy from actions menu in #Lead_1Record
    When I provide input for #LeadsDrawer.HeaderView view
      | first_name | last_name |
      | Roger      | Federer   |
    When I provide input for #LeadsDrawer.RecordView view
      | account_name    |
      | Roger's Account |
    When I click Save button on #LeadsDrawer header
    When I close alert
    Then I verify fields on #Lead_1Record.HeaderView
      | fieldName | value         |
      | name      | Roger Federer |
    Then I verify fields on #Lead_1Record.RecordView
      | fieldName    | value           |
      | account_name | Roger's Account |

  @edit_cancel
  Scenario: Leads > Record View > Edit > Cancel
    Given Leads records exist:
      | *      | first_name | last_name | account_name   | title                      | email                        |
      | Lead_1 | Rafael     | Nadal     | Rafa's Account | Professional Tennis Player | lead_1@example.net (primary) |
    Given I open about view and login
    When I choose Leads in modules menu
    When I select *Lead_1 in #LeadsList.ListView
    Then I should see #Lead_1Record view
    When I click Edit button on #Lead_1Record header
    Then I should see #Lead_1Record view
    When I provide input for #Lead_1Record.HeaderView view
      | first_name | last_name |
      | Pete       | Sampras   |
    When I provide input for #Lead_1Record.RecordView view
      | account_name   | title                       |
      | Pete's Account | 10 times French Open winner |
    When I click Cancel button on #Lead_1Record header
    Then I verify fields on #Lead_1Record.HeaderView
      | fieldName | value        |
      | name      | Rafael Nadal |
    Then I verify fields on #Lead_1Record.RecordView
      | fieldName    | value                      |
      | account_name | Rafa's Account             |
      | title        | Professional Tennis Player |

    When I click Edit button on #Lead_1Record header
    Then I should see #Lead_1Record view
    When I provide input for #Lead_1Record.HeaderView view
      | first_name | last_name |
      | Pete       | Sampras   |
    When I provide input for #Lead_1Record.RecordView view
      | account_name   | title    |
      | Pete's Account | Good Guy |
    When I click Save button on #Lead_1Record header
    When I close alert
    Then I verify fields on #Lead_1Record.HeaderView
      | fieldName | value        |
      | name      | Pete Sampras |
    Then I verify fields on #Lead_1Record.RecordView
      | fieldName    | value          |
      | account_name | Pete's Account |
      | title        | Good Guy       |

  @create
  Scenario: Leads > Create record > Cancel/Save
    Given I open about view and login
    When I choose Leads in modules menu
    When I click Create button on #LeadsList header
    When I provide input for #LeadsDrawer.HeaderView view
      | *        | first_name | last_name |
      | Lead_1 | Novak      | Djokovic  |
    When I provide input for #LeadsDrawer.RecordView view
      | *        | title                              | phone_mobile | website    |
      | Lead_1 | Serbian professional tennis player | 888-233-3221 | www.ND.com |
    # Cancel Lead creation
    When I click Cancel button on #LeadsDrawer header
    Then I should see #LeadsList.ListView view
    When I click Create button on #LeadsList header
    When I provide input for #LeadsDrawer.HeaderView view
      | *        | salutation | first_name | last_name |
      | Lead_1 | Mr.        | Novak      | Djokovic  |
    When I provide input for #LeadsDrawer.RecordView view
      | *        | title                              | phone_mobile | website               | account_name    |
      | Lead_1 | Serbian professional tennis player | 888-233-3221 | novakdjokovic.com/en/ | Novak's Account |
    # Save Lead record
    When I click Save button on #LeadsDrawer header
    When I close alert
    Then I should see *Lead_1 in #LeadsList.ListView
    When I click on preview button on *Lead_1 in #LeadsList.ListView
    # Verify the new record values in preview
    Then I should see #Lead_1Preview view
    Then I verify fields on #Lead_1Preview.PreviewView
      | fieldName    | value                              |
      | name         | Mr. Novak Djokovic                 |
      | account_name | Novak's Account                    |
      | website      | http://novakdjokovic.com/en/       |
      | phone_mobile | 888-233-3221                       |
      | title        | Serbian professional tennis player |
    # Check Audit Log
    When I select *Lead_1 in #LeadsList.ListView
    Then I verify Audit Log fields in #AuditLogDrawer for #Lead_1Record
      | fieldName        | Old Value | New Value                          |
      | first_name       |           | Novak                              |
      | last_name        |           | Djokovic                           |
      | title            |           | Serbian professional tennis player |
      | phone_mobile     |           | 888-233-3221                       |
      | team_id          |           | Global                             |
      | assigned_user_id |           | admin                              |
      | status           |           | New                                |
    # Edit record and verify Audit Log
    When I click Edit button on #Lead_1Record header
    When I provide input for #Lead_1Record.HeaderView view
      | first_name | last_name |
      | Pete       | Sampras   |
    When I click Save button on #Lead_1Record header
    When I close alert
    Then I verify Audit Log fields in #AuditLogDrawer for #Lead_1Record
      | fieldName  | before   | after   |
      | first_name | Novak    | Pete    |
      | last_name  | Djokovic | Sampras |
