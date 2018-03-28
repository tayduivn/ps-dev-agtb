# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @data_privacy
Feature: Data Privacy module verification

  Background:
    Given I use default account
    Given I launch App

  @DataPrivacy_Create_CancelSave
  Scenario: Data Privacy > Create > Cancel/Save
    Given I open about view and login
    When I go to "DataPrivacy" url
    When I click Create button on #DataPrivacyList header
    When I provide input for #DataPrivacyDrawer.HeaderView view
      | *    | name |
      | DP_1 | DP_1 |
    When I provide input for #DataPrivacyDrawer.RecordView view
      | *    | date_due   | type                         |
      | DP_1 | 11/20/2018 | Request to Erase Information |
    # Cancel DP record creation
    When I click Cancel button on #DataPrivacyDrawer header
    When I click Create button on #DataPrivacyList header
    When I provide input for #DataPrivacyDrawer.HeaderView view
      | *    | name |
      | DP_1 | DP_1 |
    When I provide input for #DataPrivacyDrawer.RecordView view
      | *    | date_due   | type                         | priority | source              | requested_by  | date_closed | description                                       |
      | DP_1 | 11/20/2018 | Request to Erase Information | Medium   | email from customer | Drew McDanial | 01/31/2019  | Customer Requested to Delete Personal Information |
    # Save DP record
    When I click Save button on #DataPrivacyDrawer header
    When I close alert
    Then I verify fields for *DP_1 in #DataPrivacyList.ListView
      | fieldName | value |
      | name      | DP_1  |
    When I click on preview button on *DP_1 in #DataPrivacyList.ListView
    Then I should see #DP_1Preview view
    Then I verify fields on #DP_1Preview.PreviewView
      | fieldName   | value                                             |
      | date_due    | 11/20/2018                                        |
      | type        | Request to Erase Information                      |
      | priority    | Medium                                            |
      | source      | email from customer                               |
      | date_closed | 01/31/2019                                        |
      | description | Customer Requested to Delete Personal Information |
      | status      | Open                                              |


  @DataPrivacy_EraseAndComplete
  Scenario: Data Privacy > Erase And Complete
    Given DataPrivacy records exist:
      | *name | type                         | priority | source     | date_due                  |
      | DP_1  | Request to Erase Information | Medium   | Phone Call | 2020-10-19T19:20:22+00:00 |
    Given Contacts records exist:
      | *    | first_name | last_name | email                                          | primary_address_city | primary_address_street | primary_address_postalcode | primary_address_state | primary_address_country | title               |
      | Alex | Alex       | Nisevich  | alex1@example.org (primary), alex2@example.org | City 1               | Street address here    | 220051                     | WA                    | USA                     | Automation Engineer |
    Given Leads records exist:
      | *    | first_name | last_name | account_name  | title             | phone_mobile   | phone_work     | primary_address  | primary_address_city | primary_address_state | primary_address_postalcode | email            |
      | John | John       | Barlow    | John's Acount | Software Engineer | (746) 079-5067 | (408) 536-6312 | 10050 N Wolfe Rd | Cupertino            | California            | 95014                      | John@example.org |
    Given Prospects records exist:
      | *      | first_name | last_name | account_name    | title             | email              | department  | phone_mobile   | phone_work     |
      | Travis | Travis     | Hubbard   | Travis's Acount | Software Engineer | travis@example.com | Engineering | (746) 079-5068 | (408) 536-6313 |
    Given Accounts records exist:
      | *name | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | email            |
      | Drew  | City 1               | Street address here    | 220051                     | WA                    | USA                     | drew@example.org |

    Given I open about view and login
    When I go to "DataPrivacy" url
    When I select *DP_1 in #DataPrivacyList.ListView
    Then I should see #DP_1Record view

    # Link Lead record to Data Privacy record
    When I open the leads subpanel on #DP_1Record view
    When I link_existing record from leads subpanel on #DP_1Record view
    When I search for "John B" in #LeadsSearchAndAdd.FilterView view
    When I toggle checkbox for *John in #LeadsSearchAndAdd.ListView
    When I click Add button on #LeadsSearchAndAdd header
    When I close alert

    # Link Contact record to Data Privacy record
    When I open the contacts subpanel on #DP_1Record view
    When I link_existing record from contacts subpanel on #DP_1Record view
    When I search for "Alex N" in #ContactsSearchAndAdd.FilterView view
    When I toggle checkbox for *Alex in #ContactsSearchAndAdd.ListView
    When I click Add button on #ContactsSearchAndAdd header
    When I close alert

    # Link Target record to Data Privacy record
    When I open the prospects subpanel on #DP_1Record view
    When I link_existing record from prospects subpanel on #DP_1Record view
    When I search for "Travis H" in #ProspectsSearchAndAdd.FilterView view
    When I toggle checkbox for *Travis in #ProspectsSearchAndAdd.ListView
    When I click Add button on #ProspectsSearchAndAdd header
    When I close alert

    # Link Account record to Data Privacy record
    When I open the accounts subpanel on #DP_1Record view
    When I link_existing record from accounts subpanel on #DP_1Record view
    When I search for "Drew" in #AccountsSearchAndAdd.FilterView view
    When I toggle checkbox for *Drew in #AccountsSearchAndAdd.ListView
    When I click Add button on #AccountsSearchAndAdd header
    When I close alert

    # Select which fields to erase for the contact
    When I click on MarkToErase button for *Alex in #DP_1Record.SubpanelsLayout.subpanels.contacts
    When I select fields in #PersonalInfoDrawer view
      | fieldName            |
      | first_name           |
      | phone_mobile         |
      | title                |
      | primary_address_city |
      | email                |
    When I click MarkForErasure button on #ContactsSearchAndAdd header

    # Select which fields to erase for the lead
    When I click on MarkToErase button for *John in #DP_1Record.SubpanelsLayout.subpanels.leads
    When I select fields in #PersonalInfoDrawer view
      | fieldName              |
      | phone_mobile           |
      | phone_other            |
      | primary_address_street |
    When I click MarkForErasure button on #LeadsSearchAndAdd header

    # Select which fields to erase for the target
    When I click on MarkToErase button for *Travis in #DP_1Record.SubpanelsLayout.subpanels.prospects
    When I select fields in #PersonalInfoDrawer view
      | fieldName    |
      | title        |
      | phone_mobile |
      | last_name    |
    When I click MarkForErasure button on #ProspectsSearchAndAdd header

    # Select which fields to erase for the account
    When I click on MarkToErase button for *Drew in #DP_1Record.SubpanelsLayout.subpanels.accounts
    When I select fields in #PersonalInfoDrawer view
      | fieldName |
      | email     |
    When I click MarkForErasure button on #AccountsSearchAndAdd header

    # Erase and Complete
    When I click EraseAndComplete button on #DP_1Record header
    When I Confirm confirmation alert
    When I close alert

    # Verify Data Privacy record status
    Then I verify fields on #DP_1Record.RecordView
      | fieldName | value     |
      | status    | Completed |

    # Update Data Privacy record resolution field
    When I click Edit button on #DP_1Record header
    When I provide input for #DP_1Record.RecordView view
      | resolution                                       |
      | The request is successfully completed by Seedbed |
    When I click Save button on #DP_1Record header
    When I close alert

    # Verify that values for specified contact fields are erased
    When I choose Contacts in modules menu
    Then I should see *Alex in #ContactsList.ListView
    When I select *Alex in #ContactsList.ListView
    Then I should see #AlexRecord view
    When I click show more button on #AlexRecord view
    Then I verify fields on #AlexRecord.HeaderView
      | fieldName | value    |
      | full_name | Nisevich |
    Then I verify fields on #AlexRecord.RecordView
      | fieldName            | value             |
      | title                | Value erased      |
      | primary_address_city | Value erased      |
      | phone_mobile         | Value erased      |
      | email                | alex2@example.org |

    # Verify that values for specified lead fields are erased
    When I choose Leads in modules menu
    Then I should see *John in #LeadsList.ListView
    When I select *John in #LeadsList.ListView
    Then I should see #JohnRecord view
    When I click show more button on #JohnRecord view
    Then I verify fields on #JohnRecord.RecordView
      | fieldName              | value        |
      | phone_mobile           | Value erased |
      | primary_address_street | Value erased |

    # Verify that values for specified target fields are erased
    When I choose Prospects in modules menu
    Then I should see *Travis in #ProspectsList.ListView
    When I select *Travis in #ProspectsList.ListView
    Then I should see #TravisRecord view
    When I click show more button on #TravisRecord view
    Then I verify fields on #TravisRecord.HeaderView
      | fieldName | value  |
      | full_name | Travis |
    Then I verify fields on #TravisRecord.RecordView
      | fieldName    | value        |
      | title        | Value erased |
      | phone_mobile | Value erased |

    # Verify that values for specified account fields are erased
    When I choose Accounts in modules menu
    Then I should see *Drew in #AccountsList.ListView
    When I select *Drew in #AccountsList.ListView
    Then I should see #DrewRecord view
    When I click show more button on #DrewRecord view
    Then I verify fields on #TravisRecord.RecordView
      | fieldName | value |
      | email     |       |


  @DataPrivacy_Reject
  Scenario: Data Privacy > Reject
    Given DataPrivacy records exist:
      | *name | type                         | priority | source     | date_due                  |
      | DP_1  | Request to Erase Information | Medium   | Phone Call | 2020-10-19T19:20:22+00:00 |
    Given Contacts records exist:
      | *    | first_name | last_name | email            | primary_address_city | primary_address_street | primary_address_postalcode | primary_address_state | primary_address_country | title               |
      | Alex | Alex       | Nisevich  | alex@example.org | City 1               | Street address here    | 220051                     | WA                    | USA                     | Automation Engineer |
    Given I open about view and login
    When I go to "DataPrivacy" url
    When I select *DP_1 in #DataPrivacyList.ListView
    Then I should see #DP_1Record view

    # Link Contact record to Data Privacy record
    When I open the contacts subpanel on #DP_1Record view
    When I link_existing record from contacts subpanel on #DP_1Record view
    When I search for "Alex N" in #ContactsSearchAndAdd.FilterView view
    When I toggle checkbox for *Alex in #ContactsSearchAndAdd.ListView
    When I click Add button on #ContactsSearchAndAdd header
    When I close alert

    # Select which fields to erase  for contact
    When I click on MarkToErase button for *Alex in #DP_1Record.SubpanelsLayout.subpanels.contacts
    When I select fields in #PersonalInfoDrawer view
      | fieledName            |
      | first_name            |
      | last_name             |
      | title                 |
      | primary_address_state |
    When I click MarkForErasure button on #ContactsSearchAndAdd header

    When I click Reject button on #DP_1Record header
    When I Confirm confirmation alert
    When I close alert

    Then I verify fields on #DP_1Record.RecordView
      | fieldName | value    |
      | status    | Rejected |

    # Verify that no data is erased for Contact
    When I choose Contacts in modules menu
    Then I should see *Alex in #ContactsList.ListView
    When I select *Alex in #ContactsList.ListView
    Then I should see #AlexRecord view
    When I click show more button on #AlexRecord view
    Then I verify fields on #AlexRecord.HeaderView
      | fieldName | value         |
      | full_name | Alex Nisevich |
    Then I verify fields on #AlexRecord.RecordView
      | fieldName             | value               |
      | title                 | Automation Engineer |
      | primary_address_state | WA                  |



