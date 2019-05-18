# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_tags @job3
Feature: Tags module verification

  Background:
    Given I am logged in

  @list
  Scenario: Tags > List View > Preview
    Given Tags records exist:
      | *   | name  | description  |
      | T_1 | name1 | description1 |

    Then Tags *T_1 should have the following values in the preview:
      | fieldName   | value        |
      | name        | name1        |
      | description | description1 |

  @list-search
  Scenario Outline: Tags > List View > Filter > Search main input
    Given 3 Tags records exist:
      | *           | name        | description          |
      | T_{{index}} | T_{{index}} | Description{{index}} |
    # Search for specific record
    When I choose Tags in modules menu
    And I search for "T_<searchIndex>" in #TagsList.FilterView view
    # Verification if filtering is successful
    Then I should see [*T_<searchIndex>] on Tags list view
    And I should not see [*T_1, *T_3] on Tags list view
    Examples:
      | searchIndex |
      | 2           |

  @list-edit
  Scenario Outline: Tags > List View > Inline Edit > Cancel/Save
    Given Tags records exist:
      | *   | name |
      | T_1 | T_1  |

    # Edit (or cancel editing of) record in the list view
    When I <action> *T_1 record in Tags list view with the following values:
      | fieldName | value           |
      | name      | T_<changeIndex> |

    # Verify if edit (or cancel) is successful
    Then Tags *T_1 should have the following values in the list view:
      | fieldName | value             |
      | name      | T_<expectedIndex> |

    Examples:
      | action            | changeIndex | expectedIndex |
      | edit              | 2           | 2             |
      | cancel editing of | 2           | 1             |

  @list-delete
  Scenario Outline: Tags > List View > Delete > OK/Cancel
    Given Tags records exist:
      | *   | name | description    |
      | T_1 | Tag1 | T_Description1 |
    # Delete (or Cancel deletion of) record from list view
    When I <action> *T_1 record in Tags list view
    # Verify that record is (is not) deleted
    Then I <expected> see [*T_1] on Tags list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |

  @delete
  Scenario Outline: Tags > Record View > Delete
    Given Tags records exist:
      | *   | name | description  |
      | T_1 | Tag1 | Description1 |

    # Delete (or Cancel deletion of) record in the record view
    When I <action> *T_1 record in Tags record view

    # Verify that record is (is not) deleted
    When I choose Tags in modules menu
    Then I <expected> see [*T_1] on Tags list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |

  @share
  Scenario Outline: Tags > Record view > Share > Cancel/Save
    Given Tags records exist:
      | *  | name    | description    |
      | T1 | <name1> | <description1> |
    # Navigate to the Tags module then record view.
    When I choose Tags in modules menu
    Then I should see *T1 in #TagsList.ListView
    When I select *T1 in #TagsList.ListView
    Then I should see #T1Record view

    # Share Tags > Cancel
    When I open actions menu in #T1Record
    When I choose Share from actions menu in #T1Record
    When I close alert
    When I click Cancel button on #EmailsDrawer header
    Then I verify fields on #T1Record.HeaderView
      | fieldName | value   |
      | name      | <name1> |

    # Share Tags > Save as Draft
    Given Accounts records exist:
      | *  | name     | email                       |
      | A1 | SugarCRM | admin@example.org (primary) |
    And Contacts records exist:
      | *  | first_name | last_name | email                      |
      | C1 | Will       | Westin    | will@example.org (primary) |
    And Leads records exist:
      | *  | first_name | last_name | email                     |
      | L1 | Max        | Jensen    | max@example.org (primary) |

    When I open actions menu in #T1Record
    When I choose Share from actions menu in #T1Record
    # Close warning about SMTP server not being configured
    When I close alert

    # Populate various email fields
    When I add the following recipients to the email in #EmailsRecord.RecordView
      | fieldName | value |
      | To        | *A1   |
      | Cc        | *C1   |
      | Bcc       | *L1   |
    When I click show more button on #EmailsDrawer view
    When I provide input for #EmailsRecord.RecordView view
      | *  | name            | parent_name      | tag                 |
      | R1 | This is a test. | Account,SugarCRM | Seedbed, Automation |

    # Save email as draft
    When I click Save button on #EmailsRecord header
    When I close alert

    # Navigate to email record view
    When I choose Emails in modules menu
    When I select *R1 in #EmailsList.ListView
    When I close alert

    # Verify data in various email fields
    Then I verify fields on #R1Record.RecordView
      | fieldName  | value                                      |
      | name       | This is a test.                            |
      | recipients | SugarCRM; Cc: Will Westin; Bcc: Max Jensen |

    Examples:
      | name1 | description1      |
      | Tag 1 | Tag Description 1 |

  @create
  Scenario: Tags > Create
    # Click Create Tag in Mega menu
    When I choose Tags in modules menu and select "Create Tag" menu item
    # Populate Header data
    When I provide input for #TagsDrawer.HeaderView view
      | *   | name |
      | T_1 | Tag1 |
    # Populate record data
    When I provide input for #TagsDrawer.RecordView view
      | *   | description  |
      | T_1 | Description1 |
    # Save
    When I click Save button on #TagsDrawer header
    When I close alert
    # Verify that record is created successfully
    Then Tags *T_1 should have the following values:
      | fieldName   | value        |
      | name        | Tag1         |
      | description | Description1 |