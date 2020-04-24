# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_contacts @job1
Feature: Contacts module verification

  Background:
    Given I am logged in

  @list
  Scenario: Contacts > List View > Preview
    Given Contacts records exist:
      | *         | first_name | last_name | title             | phone_mobile   | phone_work     | primary_address_street | primary_address_city | primary_address_state | primary_address_postalcode | email            |
      | Contact_1 | ContactF   | ContactL  | Software Engineer | (746) 079-5067 | (408) 536-6312 | 10050 N Wolfe Rd       | Cupertino            | California            | 95014                      | John@example.org |

    Then Contacts *Contact_1 should have the following values in the preview:
      | fieldName              | value             |
      | name                   | ContactF ContactL |
      | title                  | Software Engineer |
      | email                  | John@example.org  |
      | phone_mobile           | (746) 079-5067    |
      | phone_work             | (408) 536-6312    |
      | primary_address_street | 10050 N Wolfe Rd  |


  @list-search
  Scenario Outline: Contacts > List View > Filter > Search main input
    Given 3 Contacts records exist:
      | *                 | first_name       | last_name        | title                      | email                         |
      | Contact_{{index}} | Contact{{index}} | Contact{{index}} | Software Engineer{{index}} | Contact_{{index}}@example.net |
    # Search for specific record
    When I choose Contacts in modules menu
    And I search for "Contact<searchIndex>" in #ContactsList.FilterView view
    # Verification if filtering is successful
    Then I should see [*Contact_<searchIndex>] on Contacts list view
    And I should not see [*Contact_1, *Contact_3] on Contacts list view
    Examples:
      | searchIndex |
      | 2           |


  @list-edit
  Scenario Outline: Contacts > List View > Inline Edit > Cancel/Save
    Given Contacts records exist:
      | *         | first_name | last_name | phone_work     | title               | email                 |
      | Contact_1 | Contact1   | Contact1  | (408) 536-0001 | Software Engineer 1 | Contact_1@example.net |

    # Edit (or cancel editing of) record in the list view
    When I <action> *Contact_1 record in Contacts list view with the following values:
      | fieldName  | value                             |
      | first_name | Contact<changeIndex>              |
      | last_name  | Contact<changeIndex>              |
      | title      | Software Engineer <changeIndex>   |
      | phone_work | (408) 536-000<changeIndex>        |
      | email      | Contact_<changeIndex>@example.net |

    # Verify if edit (or cancel) is successful
    Then Contacts *Contact_1 should have the following values in the list view:
      | fieldName  | value                                         |
      | name       | Contact<expectedIndex> Contact<expectedIndex> |
      | title      | Software Engineer <expectedIndex>             |
      | email      | Contact_<expectedIndex>@example.net           |
      | phone_work | (408) 536-000<expectedIndex>                  |

    Examples:
      | action            | changeIndex | expectedIndex |
      | edit              | 2           | 2             |
      | cancel editing of | 2           | 1             |


  @list-delete
  Scenario Outline: Contacts > List View > Delete > OK/Cancel
    Given Contacts records exist:
      | *         | first_name | last_name | phone_work     | title             | email                 |
      | Contact_1 | Contact1   | Contact1  | (408) 536-0000 | Software Engineer | Contact_1@example.net |
    # Delete (or Cancel deletion of) record from list view
    When I <action> *Contact_1 record in Contacts list view
    # Verify that record is (is not) deleted
    Then I <expected> see [*Contact_1] on Contacts list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |


  @delete
  Scenario Outline: Contacts > Record View > Delete
    Given Contacts records exist:
      | *         | first_name | last_name | phone_work     | title             | email                 |
      | Contact_1 | Contact1   | Contact1  | (408) 536-0000 | Software Engineer | Contact_1@example.net |

    # Delete (or Cancel deletion of) record in the record view
    When I <action> *Contact_1 record in Contacts record view

    # Verify that record is (is not) deleted
    When I choose Contacts in modules menu
    Then I <expected> see [*Contact_1] on Contacts list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |


  @copy
  Scenario Outline: Contacts > Record View > Copy > Save/Cancel
    Given Contacts records exist:
      | *         | first_name | last_name | title             | email                           |
      | Contact_1 | Contact1   | Contact1  | Software Engineer | Contact_1@example.net (primary) |

   # Copy (or cancel copy of) record in the record view
    When I <action> *Contact_1 record in Contacts record view with the following header values:
      | *         | first_name           | last_name            |
      | Contact_2 | Contact<changeIndex> | Contact<changeIndex> |

   # Verify if copy is (is not) created
    Then Contacts *Contact_<expectedIndex> should have the following values:
      | fieldName | value                                         |
      | name      | Contact<expectedIndex> Contact<expectedIndex> |
      | title     | Software Engineer                             |
      | email     | Contact_1@example.net                         |

    Examples:
      | action         | changeIndex | expectedIndex |
      | cancel copy of | 2           | 1             |
      | copy           | 2           | 2             |


  @create
  Scenario: Contacts > Create
    # Click Create Contact in Mega menu
    When I choose Contacts in modules menu and select "Create Contact" menu item
    When I click show more button on #ContactsDrawer view
    # Populate Header data
    When I provide input for #ContactsDrawer.HeaderView view
      | *         | salutation | first_name | last_name |
      | Contact_1 | Dr.        | Contact1   | Contact1  |
    # Populate record data
    When I provide input for #ContactsDrawer.RecordView view
      | *         | phone_work     | title | email            | phone_mobile   | department  | do_not_call | tag      | primary_address_street | primary_address_city | primary_address_state | primary_address_postalcode | description | copy |
      | Contact_1 | (408) 233-3221 | CTO   | pr_1@example.net | (408) 322-4321 | Engineering | True        | contact1 | 4307 Emperor Blvd.     | Durham               | NC                    | 27703                      | New Contact | true |
    # Save
    When I click Save button on #ContactsDrawer header
    When I close alert
    # Verify that record is created successfully
    Then Contacts *Contact_1 should have the following values:
      | fieldName                  | value                 |
      | name                       | Dr. Contact1 Contact1 |
      | title                      | CTO                   |
      | email                      | pr_1@example.net      |
      | phone_mobile               | (408) 322-4321        |
      | department                 | Engineering           |
      | do_not_call                | true                  |
      | primary_address_street     | 4307 Emperor Blvd.    |
      | primary_address_city       | Durham                |
      | primary_address_state      | NC                    |
      | primary_address_postalcode | 27703                 |
      | description                | New Contact           |
      | alt_address_street         | 4307 Emperor Blvd.    |
      | alt_address_city           | Durham                |
      | alt_address_state          | NC                    |
      | alt_address_postalcode     | 27703                 |
