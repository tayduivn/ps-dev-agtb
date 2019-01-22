# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_email @job6
Feature: Emails module verification

  Background:
    Given I use default account
    Given I launch App

  @list
  Scenario Outline: Emails > List View > Contain pre-created record
    Given Emails records exist:
      | *id | name       | state    |
      | R1  | <subject1> | <state1> |
    Given I open about view and login
    When I choose Emails in modules menu
    Then I should see *R1 in #EmailsList.ListView
    Then I verify fields for *R1 in #EmailsList.ListView
      | fieldName | value      |
      | name      | <subject1> |
      | state     | <state1>   |

    Examples:
      | subject1        | state1 |
      | This is a test. | Draft  |

  @list-preview
  Scenario Outline: Emails > List View > Preview > Check fields
    Given Emails records exist:
      | *id | name       | state    |
      | R1  | <subject1> | <state1> |
    Given I open about view and login
    When I choose Emails in modules menu
    When I click on preview button on *R1 in #EmailsList.ListView
    Then I should see #R1Preview view
    Then I verify fields on #R1Preview.PreviewView
      | fieldName | value      |
      | name      | <subject1> |

    Examples:
      | subject1        | state1 |
      | This is a test. | Draft  |

  @list-search
  Scenario Outline: Emails > List View > Filter > Search main input
    Given Emails records exist:
      | *  | name       | state    |
      | R1 | <subject1> | <state1> |
    Given I open about view and login
    When I choose Emails in modules menu
    Then I should see #EmailsList.ListView view
    When I search for "<subject1>" in #EmailsList.FilterView view
    Then I should see *R1 in #EmailsList.ListView
    Then I verify fields for *R1 in #EmailsList.ListView
      | fieldName | value      |
      | name      | <subject1> |
      | state     | <state1>   |

    Examples:
      | subject1        | state1 |
      | This is a test. | Draft  |

  @list-select-predefined-filters
  Scenario Outline: Emails > List View > Filter > Select Predefined Filter
    Given Emails records exist:
      | *  | name       | state    |
      | R1 | <subject1> | <state1> |
    Given I open about view and login
    When I choose Emails in modules menu
    Then I should see #EmailsList.ListView view
    When I choose for <filter1> in #EmailsList.FilterView view
    Then I should see *R1 in #EmailsList.ListView
    Then I verify fields for *R1 in #EmailsList.ListView
      | fieldName | value      |
      | name      | <subject1> |
      | state     | <state1>   |

    Examples:
      | filter1     | subject1         | state1 |
      | my_drafts   | This is a test.  | Draft  |
      | all_records | All Mail Filter. | Draft  |
#      | assigned_to_me   | Assigned to Me.   | Draft  |
#      | favorites        | My Favorites.     | Draft  |
#      | my_received      | My Received.      | Draft  |
#      | my_sent          | My Sent.          | Draft  |
#      | recently_created | Recently Created. | Draft  |
#      | recently_viewed  | Recently Viewed.  | Draft  |

  @email-cases-reply
  Scenario: Emails > List View > Open Record View > Edit Email > Set Related To Field > Save Email > Reply to Email > Verify Email Recipient and Subject
    Given Contacts records exist:
      | *name     |
      | Contact_1 |
    Given Cases records exist related via cases link:
      | *name  |
      | Case_1 |
    Given Emails records exist:
      | *  | name | state    | description_html |
      | R1 | Test | Archived | <p>hello</p>     |
    Given I open about view and login
    When I choose Emails in modules menu
    Then I should see *R1 in #EmailsList.ListView
    When I select *R1 in #EmailsList.ListView
    Then I should see #R1Record.RecordView view
    When I click show more button on #R1Record view
    When I open actions menu in #R1Record
    Then I click Edit button on #R1Record header
    When I provide input for #R1Record.RecordView view
      | parent_name |
      | Case,Case_1 |
    When I click Save button on #R1Record header
    Then I close alert
    When I click Reply button on #R1Record header
    When I click recipients field on #EmailsRecord.RecordView view
    Then I verify fields on #EmailsRecord.RecordView
      | fieldName     | value    |
      | name          | Re: Test |
      | to_collection |          |
    When I close alert
    When I click Cancel button on #EmailsRecord header

  @email-cases-compose
  Scenario: Cases > List View > Open Record View > Create Email from Emails Subpanel
    Given Cases records exist:
      | *      | name    |
      | Case_1 | My Case |
    Given Contacts records exist related via contacts link:
      | *         | first_name | last_name |
      | Contact_1 | Adam       | Taylor    |
      | Contact_2 | Richie     | Gomes     |
    Given I open about view and login
    When I choose Cases in modules menu
    Then I should see *Case_1 in #CasesList.ListView
    When I select *Case_1 in #CasesList.ListView
    Then I should see #Case_1Record.RecordView view
    When I open the archived_emails subpanel on #Case_1Record view
    When I create_new record from archived_emails subpanel on #Case_1Record view
    When I click recipients field on #EmailsRecord.RecordView view
    Then I verify fields on #EmailsRecord.RecordView
      | fieldName     | value                                |
      | name          | [CASE:{*Case_1.case_number}] My Case |
      | to_collection | Richie Gomes,Adam Taylor             |
    When I close alert
    When I click Cancel button on #EmailsRecord header

  @email-signatures
  Scenario: Emails > Create Email Signature
    Given UserSignatures records exist:
      | *  | name  | signature_html          |
      | R1 | Sig_1 | <p>My Signature AAA</p> |
    Given I open about view and login
    When I go to "UserSignatures" url
    When I select *R1 in #UserSignaturesList.ListView

    # Verify UserSignature record is created successfully
    Then I verify fields on #R1Record.HeaderView
      | fieldName | value |
      | name      | Sig_1 |
    Then I verify fields on #R1Record.RecordView
      | fieldName      | value                   |
      | signature_html | <p>My Signature AAA</p> |

    # Update signature
    When I update UserSignatures *R1 with the following values:
      | signature_html                                 |
      | <h3>My Signature BBB</h3><br><h2>SugarCRM</h2> |

    # Verify signature value
    Then I verify fields on #R1Record.RecordView
      | fieldName      | value                                                                        |
      | signature_html | <h3>My Signature BBB</h3><p><br></p><h2>SugarCRM</h2><p>My Signature AAA</p> |

    # Delete Signature
    When I open actions menu in #R1Record and check:
      | menu_item | active |
      | Delete    | true   |
    When I choose Delete from actions menu in #R1Record
    When I Confirm confirmation alert
    Then I verify number of records in #UserSignaturesList.ListView is 0

  @email_compose_manual
  Scenario: Emails > Compose Email > Add addressees through Address Book and Populate Email Body
    Given Accounts records exist:
      | *   | name     | email                           |
      | A_1 | SugarCRM | sugarcrm@sugarcrm.com (primary) |
      | A_2 | IBM      | ibm@sugarcrm.com (primary)      |
    And Contacts records exist:
      | *   | first_name | last_name | email                       |
      | C_1 | Rich       | Green     | rich@example.org (primary)  |
      | C_2 | Jorge      | Arroyo    | jorge@example.org (primary) |
    And Leads records exist:
      | *   | first_name | last_name | email                         |
      | L_1 | Steve      | Parsley   | stive@example.org (primary)   |
      | L_2 | Sandhya    | Jaideep   | sandhya@example.org (primary) |

    Given I open about view and login
    When I choose Emails in modules menu
    When I click Create button on #EmailsList header
    When I close alert
    # Add email recipients from Address Book
    When I add the following recipients to the email in #EmailsRecord.RecordView
      | fieldName | value |
      | To        | *A_1  |
      | Cc        | *C_1  |
      | Bcc       | *L_1  |
    # Populate various email fields
    When I click show more button on #EmailsRecord view
    When I provide input for #EmailsRecord.RecordView view
      | *  | name            | description_html     | parent_name      | tag                 |
      | R1 | This is a test. | <h1>Lorem ipsum</h1> | Account,SugarCRM | Seedbed, Automation |

    # Verify email body is populated
    Then I verify fields on #EmailsRecord.RecordView
      | fieldName        | value                |
      | name             | This is a test.      |
      | description_html | <h1>Lorem ipsum</h1> |

    # Save email as draft
    When I click Save button on #EmailsRecord header
    When I close alert

    # Verify email message name and state in the list view
    Then I verify fields for *R1 in #EmailsList.ListView
      | fieldName | value           |
      | name      | This is a test. |
      | state     | Draft           |

    # Navigate to email record view
    When I select *R1 in #EmailsList.ListView
    When I close alert

    # Verify data in various email fields
    Then I verify fields on #R1Record.RecordView
      | fieldName        | value                                        |
      | name             | This is a test.                              |
      | description_html | <h1>Lorem ipsum</h1>                         |
      | recipients       | SugarCRM; Cc: Rich Green; Bcc: Steve Parsley |

    # Add more email recipients
    When I add the following recipients to the email in #EmailsRecord.RecordView
      | fieldName | value |
      | To        | *A_2  |
      | Cc        | *C_2  |
      | Bcc       | *L_2  |

    # Update email subject and body
    When I provide input for #R1Record.RecordView view
      | *  | name                | description_html        |
      | R1 | This is a test too. | <h2>dolor sit amet</h2> |

    # Save
    When I click Save button on #EmailsRecord header
    When I close alert
    When I select *R1 in #EmailsList.ListView
    When I close alert

    # Verify that list of recipients is correct
    Then I verify fields on #R1Record.RecordView
      | fieldName  | value                                                                            |
      | recipients | SugarCRM, IBM; Cc: Rich Green, Jorge Arroyo; Bcc: Steve Parsley, Sandhya Jaideep |
    When I click Cancel button on #EmailsRecord header

    # Verify that saved email appears in Account record view > Emails subpanel
    When I choose Accounts in modules menu
    When I select *A_1 in #AccountsList.ListView
    When I open the archived_emails subpanel on #A_1Record view
    Then I verify fields for *R1 in #A_1Record.SubpanelsLayout.subpanels.archived_emails
      | fieldName | value               |
      | name      | This is a test too. |
      | state     | Draft               |

  @email_compose_manual
  Scenario: Emails > Compose Email > Type-in Email Recipients
    Given Accounts records exist:
      | *   | name     | email                           |
      | A_1 | SugarCRM | sugarcrm@sugarcrm.com (primary) |
      | A_2 | IBM      | ibm@sugarcrm.com (primary)      |
    Given I open about view and login
    When I choose Emails in modules menu
    When I click Create button on #EmailsList header
    When I close alert
    # Fill out email 'To' and 'Bcc' Fields
    When I click CC button on #EmailsDrawer.RecordView
    When I provide input for #EmailsDrawer.RecordView view
      | *  | name            | cc_collection | to_collection         |
      | R1 | This is a test. | b_22@ccc.com  | sugarcrm@sugarcrm.com |
    # Activate and fill-out 'Bcc' field
    When I click BCC button on #EmailsDrawer.RecordView
    When I provide input for #EmailsDrawer.RecordView view
      | bcc_collection                 |
      | b_33@bcc.com, ibm@sugarcrm.com |
    When I click Save button on #EmailsDrawer header
    When I close alert
    When I select *R1 in #EmailsList.ListView
    When I close alert
    # Verify results
    Then I verify fields on #EmailsRecord.RecordView
      | fieldName  | value                                              |
      | recipients | SugarCRM; Cc: b_22@ccc.com; Bcc: b_33@bcc.com, IBM |