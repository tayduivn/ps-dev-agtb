# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules @emails-group 
Feature: Emails module verification

  Background:
    Given I use default account
    Given I launch App with config: "skipTutorial"

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
      | filter1          | subject1          | state1 |
      | my_drafts        | This is a test.   | Draft  |
      | all_records      | All Mail Filter.  | Draft  |
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
      | *  | name | state       | description_html |
      | R1 | Test | Archived    | <p>hello</p>     |
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
    Then I verify fields on #EmailsRecord.RecordView
      | fieldName     | value    |
      | name          | Re: Test |
      | to_collection | ,        |

  @email-signatures
  Scenario Outline: Emails > Create Email Signature
    Given I open about view and login
    When I go to "UserSignatures" url
    When I click Create button on #UserSignaturesList header
    Then I should see #UserSignaturesRecord.RecordView view
    When I provide input for #UserSignaturesRecord.HeaderView view
      | *  | name       |
      | R1 | <subject1> |
    When I click Save button on #UserSignaturesRecord header
    When I close alert
    Then I should see *R1 in #UserSignaturesList.ListView
    Then I verify fields for *R1 in #UserSignaturesList.ListView
      | fieldName | value      |
      | name      | <subject1> |
    When I select *R1 in #UserSignaturesList.ListView
    When I click Edit button on #R1Record header
    When I provide input for #R1Record.HeaderView view
      | *  | name                |
      | R1 | This is a test too. |
    When I click Save button on #R1Record header
    When I close alert
    When I go to "UserSignatures" url
    Then I verify fields for *R1 in #UserSignaturesList.ListView
      | fieldName | value               |
      | name      | This is a test too. |
    When I select *R1 in #UserSignaturesList.ListView
    When I open actions menu in #R1Record and check:
      | menu_item | active |
      | Delete    | true   |

    Examples:
      | subject1        |
      | This is a test. |

  @email_compose_manual
  Scenario Outline: Emails > Compose Email
    Given I open about view and login
    When I choose Emails in modules menu
    When I click Create button on #EmailsList header
    When I close alert
    Then I should see #EmailsRecord.RecordView view
    When I provide input for #EmailsRecord.RecordView view
#  *commented out section is functionality not available yet in seedbed. Also
#  add a signature, an attachment, and/or a template, is not currently supported in seedbed.
#  Test now works with variables!
#      | *  | to_collection       | name            |
#      | R1 | qatest@sugarcrm.com | This is a test. |
      | *  | name            |
      | R1 | This is a test. |
    When I click Save button on #EmailsRecord header
    When I close alert
    Then I should see #EmailsList.ListView view
    Then I should see *R1 in #EmailsList.ListView
#    Then I wait for 120 seconds
    Then I verify fields for *R1 in #EmailsList.ListView
      | fieldName | value           |
      | name      | This is a test. |
    When I select *R1 in #EmailsList.ListView
    When I close alert
    Then I should see #R1Record.RecordView view
    When I provide input for #R1Record.RecordView view
      | *  | name                |
      | R1 | This is a test too. |
    When I click Save button on #R1Record header
    When I close alert
    Then I should see *R1 in #EmailsList.ListView
    Then I verify fields for *R1 in #EmailsList.ListView
      | fieldName | value               |
      | name      | This is a test too. |
    Examples:
      | emails1             | subject1        | state1 | body1                      |
      | qatest@sugarcrm.com | This is a test. | draft  | Lorem ipsum dolor sit amet |

#Test below not currently functional, needs a few step definitions created.
#  @email_create_alt_methods
#  Scenario Outline: Emails > Compose Email > Quick Compose > Click Email Address > Click Email Address Subpanel.
#    Given Contacts records exist:
#      | *  | email                  | first name | last name |
#      | R1 | vegan.sales@example.de | qa         | test      |
#    Given I open about view and login
#    When I choose Contacts in modules menu
#    Then I should see #ContactsList.ListView view
#    When I select *R1 in #EmailsList.ListView
#    When I close alert
#    Then I should see #R1Record.RecordView view
#    When I provide input for #R1Record.RecordView view
#      | *  | name                |
#      | R1 | This is a test too. |
#    When I click Save button on #R1Record header
#    When I close alert
#    Then I should see *R1 in #EmailsList.ListView
#    Then I verify fields for *R1 in #EmailsList.ListView
#      | fieldName | value               |
#      | name      | This is a test too. |
#    When I select *R1 in #EmailsRecord.ListView
#    When I open actions menu in #R1Record and check:
#      | menu_item | active |
#      | Delete    | true   |
#
#    Examples:
#      | emails1             | subject1        | state1 | body1                                                                                |
#      | qatest@sugarcrm.com | This is a test. | draft  | Lorem ipsum dolor sit amet, neo qudsdne commodo ibidem patria os dolus ut antehabeo. |
