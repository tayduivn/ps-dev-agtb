# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@activity_stream @job2
Feature: Activity Stream Feature verification

  Background:
    Given I use default account
    Given I launch App

  @pr
  Scenario: Data Privacy > Erase data from activity stream messages
    Given DataPrivacy records exist:
      | *name | type                         | priority | source     | date_due                  |
      | DP_1  | Request to Erase Information | Medium   | Phone Call | 2020-10-19T19:20:22+00:00 |
    And Calls records exist:
      | *name  | assigned_user_id | date_start                | duration_hours | duration_minutes | direction | reminder_time | email_reminder_time | description      | status  |
      | Call_1 | 1                | 2020-04-16T14:30:00-07:00 | 0              | 45               | Outbound  | 0             | 0                   | Call to customer | Planned |
    And Contacts records exist:
      | first_name | last_name | phone_home     |
      | Travis     | Hubbard   | (798) 852-5170 |

    Given I open about view and login

    # Create contact record
    When I choose Contacts in modules menu
    When I click Create button on #ContactsList header
    When I provide input for #ContactsRecord.HeaderView view
      | *   | first_name | last_name |
      | C_1 | Alexander  | Nisevich  |
    When I provide input for #ContactsRecord.RecordView view
      | *   | title       |
      | C_1 | Software QE |
    When I click Save button on #ContactsRecord header
    When I close alert

    # Link another record in Activity Stream message
    When I select ActivityStream in #ContactsList.FilterView
    When I post the following activities to #ActivityStream
      | message          |
      | Hello #Travis Hu |

    # Verify that record is linked properly
    Then I verify activities in #ActivityStream
      | message                             |
      | Hello Travis Hubbard on Contact.    |
      | Created Alexander Nisevich Contact. |

    # Comment on the top activity
    When I comment on the top activity in #ActivityStream
      | comment                   |
      | My New Comment activity 1 |
      | My New Comment activity 2 |

    # Link Contact (C_1) to Data Privacy record (DP_1)
    When I go to "DataPrivacy" url
    When I select *DP_1 in #DataPrivacyList.ListView
    Then I should see #DP_1Record view
    When I link existing record *C_1 to contacts subpanel on #DP_1Record view

    # Select which fields to erase for contact Alexander
    When I select fields for erasure for *C_1 record in #DP_1Record.SubpanelsLayout.subpanels.contacts subpanel
      | fieldName  |
      | first_name |
      | last_name  |

    # Erase and Complete
    When I eraseandcomplete the Data Privacy request on #DP_1Record

    # Link Contact (C_1) to Call (Call_1)
    When I choose Contacts in modules menu
    When I select ListView in #ContactsList.FilterView
    When I select *C_1 in #ContactsList.ListView
    When I link existing record *Call_1 to calls subpanel on #C_1Record view

    # UnLink Contact (C_1) from Call
    When I unlink existing record *Call_1 from calls subpanel on #C_1Record.SubpanelsLayout.subpanels.calls view

    # Verify messages in Activity Stream in the Contact Record view
    When I select ActivityStream in #ContactsList.FilterView
    Then I verify activities in #ActivityStream
      | message                                        |
      | Unlinked Value Erased to Call_1.               |
      | Linked Value Erased to Call_1.                 |
      | Updated Last Name, First Name on Value Erased. |
      | Linked DP_1 to Alexander Nisevich.             |
      | Created Alexander Nisevich Contact.            |

    # Navigate to Contacts list view
    When I choose Contacts in modules menu
    When I select ActivityStream in #ContactsList.FilterView
    # Try to link the record with deleted first and last name
    When I post the following activities to #ActivityStream
      | message              |
      | Hello #Alexander Nis |

    # Verify messages in Activity Stream in the Contact List view
    Then I verify activities in #ActivityStream
      | message                                        |
      | Hello #Alexander Nis on Contact.               |
      | Updated Last Name, First Name on Value Erased. |
      | Linked DP_1 to Alexander Nisevich.             |
      | Hello Travis Hubbard on Contact.               |
      | Created Alexander Nisevich Contact.            |

    # Verify messages in Activity Stream under Home Cube
    When I go to "activities" url
    Then I verify activities in #ActivityStream
      | activity_message                               |
      | Unlinked Value Erased to Call_1.               |
      | Linked Value Erased to Call_1.                 |
      | Updated Last Name, First Name on Value Erased. |
      | Linked DP_1 to Alexander Nisevich.             |
      | Created Alexander Nisevich Contact.            |
