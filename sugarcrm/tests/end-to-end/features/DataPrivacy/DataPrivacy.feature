# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@data_privacy
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


  @DataPrivacy_EraseAndComplete @pr
  Scenario: Data Privacy > Erase And Complete
    Given DataPrivacy records exist:
      | *name | type                         | priority | source     | date_due                  |
      | DP_1  | Request to Erase Information | Medium   | Phone Call | 2020-10-19T19:20:22+00:00 |
    Given Contacts records exist:
      | *      | first_name | last_name | email                                          | primary_address_city | primary_address_street | primary_address_postalcode | primary_address_state | primary_address_country | title               |
      | Alex   | Alex       | Nisevich  | alex1@example.org (primary), alex2@example.org | City 1               | Street address here    | 220051                     | WA                    | USA                     | Automation Engineer |
      | Ruslan | Ruslan     | Golovach  | rus1@example.org (primary), rus2@example.org   | City 1               | Street address here    | 220051                     | WA                    | USA                     | Automation Engineer |
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

    # Link records to Data Privacy record
    When I link existing record *John to leads subpanel on #DP_1Record view
    When I link existing record *Alex to contacts subpanel on #DP_1Record view
    When I link existing record *Ruslan to contacts subpanel on #DP_1Record view
    When I link existing record *Travis to prospects subpanel on #DP_1Record view
    When I link existing record *Drew to accounts subpanel on #DP_1Record view

    # Select which fields to erase for contact Alex
    When I select fields for erasure for *Alex record in #DP_1Record.SubpanelsLayout.subpanels.contacts subpanel
      | fieldName            |
      | first_name           |
      | last_name            |
      | phone_mobile         |
      | title                |
      | primary_address_city |
      | email                |

    # Select which fields to erase for contact Ruslan
    When I select fields for erasure for *Ruslan record in #DP_1Record.SubpanelsLayout.subpanels.contacts subpanel
      | fieldName            |
      | first_name           |
      | last_name            |
      | phone_mobile         |
      | title                |
      | primary_address_city |

    # Select which fields to erase for lead
    When I select fields for erasure for *John record in #DP_1Record.SubpanelsLayout.subpanels.leads subpanel
      | fieldName              |
      | phone_mobile           |
      | phone_other            |
      | primary_address_street |

    # Select which fields to erase for target
    When I select fields for erasure for *Travis record in #DP_1Record.SubpanelsLayout.subpanels.prospects subpanel
      | fieldName    |
      | title        |
      | phone_mobile |
      | last_name    |

    # Select which fields to erase for account
    When I select fields for erasure for *Drew record in #DP_1Record.SubpanelsLayout.subpanels.accounts subpanel
      | fieldName |
      | email     |

    # Verify number of fields marked for erasure in Records Marked for Erasure Dashlet
    Then I verify records on #Dashboard.RecordsMarkedForErasureDashlet
      | record  | fields_to_erase_count |
      | *Alex   | 6                     |
      | *Ruslan | 5                     |
      | *John   | 3                     |
      | *Travis | 3                     |
      | *Drew   | 1                     |

    # Verify number of records marked for erasure in each module in Records Marked for Erasure Dashlet
    Then I verify headers on #Dashboard.RecordsMarkedForErasureDashlet
      | header    | records_to_erase_count |
      | Contacts  | 2                      |
      | Leads     | 1                      |
      | Prospects | 1                      |
      | Accounts  | 1                      |

    # Erase and Complete
    When I eraseandcomplete the Data Privacy request on #DP_1Record

    # Verify Data Privacy record status
    Then I verify fields on #DP_1Record.RecordView
      | fieldName | value     |
      | status    | Completed |

    # Update Data Privacy record resolution field
    When I provide input for #DP_1Record
      | resolution                                       |
      | The request is successfully completed by Seedbed |

    # Verify that values for specified lead fields are erased
    When I choose Leads in modules menu
    Then I should see *John in #LeadsList.ListView
    When I select *John in #LeadsList.ListView
    Then I should see #JohnRecord view
    When I click show more button on #JohnRecord view
    Then I verify fields on #JohnRecord.RecordView
      | fieldName              | value        |
      | phone_mobile           | Value Erased |
      | primary_address_street | Value Erased |

    # Verify that info is deleted from PII screen
    Then I verify PII fields in #PersonalInfoDrawer for #JohnRecord
      | fieldName              | value        |
      | phone_mobile           | Value Erased |
      | primary_address_street | Value Erased |

    # Verify that values for specified target fields are erased
    When I choose Prospects in modules menu
    When I select *Travis in #ProspectsList.ListView
    When I click show more button on #TravisRecord view
    Then I verify fields on #TravisRecord.HeaderView
      | fieldName | value  |
      | name      | Travis |
    Then I verify fields on #TravisRecord.RecordView
      | fieldName    | value        |
      | title        | Value Erased |
      | phone_mobile | Value Erased |

    # Verify that info is deleted from PII screen
    Then I verify PII fields in #PersonalInfoDrawer for #TravisRecord
      | fieldName    | value        |
      | first_name   | Travis       |
      | last_name    | Value Erased |
      | title        | Value Erased |
      | phone_mobile | Value Erased |

    # Verify that values for specified account fields are erased
    When I choose Accounts in modules menu
    When I select *Drew in #AccountsList.ListView
    When I click show more button on #DrewRecord view
    Then I verify fields on #TravisRecord.RecordView
      | fieldName | value |
      | email     |       |

    # Verify that info is deleted from PII screen
    Then I verify PII fields in #PersonalInfoDrawer for #DrewRecord
      | fieldName | value |
      | email     |       |

    # Verify that values for specified contact fields are erased
    When I choose Contacts in modules menu
    When I select *Ruslan in #ContactsList.ListView
    When I click show more button on #RuslanRecord view
    Then I verify fields on #RuslanRecord.HeaderView
      | fieldName | value        |
      | name      | Value Erased |
    Then I verify fields on #RuslanRecord.RecordView
      | fieldName            | value        |
      | title                | Value Erased |
      | primary_address_city | Value Erased |
      | phone_mobile         | Value Erased |

    # Verify that info is deleted from PII screen
    Then I verify PII fields in #PersonalInfoDrawer for #RuslanRecord
      | fieldName            | value        |
      | first_name           | Value Erased |
      | last_name            | Value Erased |
      | title                | Value Erased |
      | primary_address_city | Value Erased |
      | phone_mobile         | Value Erased |

    # Verify that values for specified contact fields are erased
    When I choose Contacts in modules menu
    When I select *Alex in #ContactsList.ListView
    When I click show more button on #AlexRecord view
    Then I verify fields on #AlexRecord.HeaderView
      | fieldName | value        |
      | name      | Value Erased |
    Then I verify fields on #AlexRecord.RecordView
      | fieldName            | value             |
      | title                | Value Erased      |
      | primary_address_city | Value Erased      |
      | phone_mobile         | Value Erased      |
      | email                | alex2@example.org |

    # Verify that info is deleted from PII screen
    Then I verify PII fields in #PersonalInfoDrawer for #AlexRecord
      | fieldName            | value             |
      | first_name           | Value Erased      |
      | last_name            | Value Erased      |
      | title                | Value Erased      |
      | primary_address_city | Value Erased      |
      | phone_mobile         | Value Erased      |
      | email                | alex2@example.org |

    # Create Quote record from Quotes_BillTo subpanel of contact record view
    When I open the billing_quotes subpanel on #AlexRecord view
    # Create record from the subpanel
    When I create_new record from billing_quotes subpanel on #AlexRecord view
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    When I provide input for #QuotesRecord.HeaderView view
      | *      | name         |
      | Quote1 | My New Quote |
    When I provide input for #QuotesRecord.RecordView view
      | *      | date_quote_expected_closed | billing_account_name |
      | Quote1 | 12/12/2020                 | Drew                 |
    When I Confirm confirmation alert
    When I click Save button on #QuotesRecord header
    When I close alert
    When I toggle Billing_and_Shipping panel on #Quote1Record.RecordView view
    Then I should see #Quote1Record view
    Then I verify fields on #Quote1Record.HeaderView
      | fieldName | value        |
      | name      | My New Quote |

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
    When I link existing record *Alex to contacts subpanel on #DP_1Record view

    # Select which fields to erase  for contact
    When I select fields for erasure for *Alex record in #DP_1Record.SubpanelsLayout.subpanels.contacts subpanel
      | fieledName            |
      | first_name            |
      | last_name             |
      | title                 |
      | primary_address_state |

    # Reject erasure request
    When I reject the Data Privacy request on #DP_1Record

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
      | name      | Alex Nisevich |
    Then I verify fields on #AlexRecord.RecordView
      | fieldName             | value               |
      | title                 | Automation Engineer |
      | primary_address_state | WA                  |
