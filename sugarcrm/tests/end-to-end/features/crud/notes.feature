# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_notes @job1
Feature: Notes module verification

  Background:
    Given I am logged in

  @list
  Scenario: Notes > List View > Preview
    Given Notes records exist:
      | *   | name   | description        |
      | N_1 | Note 1 | Note 1 Description |

    Then Notes *N_1 should have the following values in the preview:
      | fieldName   | value              |
      | name        | Note 1             |
      | description | Note 1 Description |

  @list-delete
  Scenario Outline: Notes > List View > Delete > OK/Cancel
    Given Notes records exist:
      | *   | name  |
      | N_1 | Note1 |
    # Delete (or Cancel deletion of) record from list view
    When I <action> *N_1 record in Notes list view
    # Verify that record is (is not) deleted
    Then I <expected> see [*N_1] on Notes list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |

  @list-search
  Scenario Outline: Prospects > List View > Filter > Search main input
    Given 3 Notes records exist:
      | *           | name          | description          |
      | N_{{index}} | Note{{index}} | Description{{index}} |
    # Search for specific record
    When I choose Notes in modules menu
    And I search for "Note<searchIndex>" in #NotesList.FilterView view
    # Verification if filtering is successful
    Then I should see [*N_<searchIndex>] on Notes list view
    And I should not see [*N_1, *N_3] on Notes list view
    Examples:
      | searchIndex |
      | 2           |

  @list-edit
  Scenario Outline: Notes > List View > Inline Edit > Cancel/Save
    Given Notes records exist:
      | *   | name  |
      | N_1 | Note1 |

    # Edit (or cancel editing of) record in the list view
    When I <action> *N_1 record in Notes list view with the following values:
      | fieldName | value             |
      | name      | Note<changeIndex> |

    # Verify if edit (or cancel) is successful
    Then Notes *N_1 should have the following values in the list view:
      | fieldName | value               |
      | name      | Note<expectedIndex> |

    Examples:
      | action            | changeIndex | expectedIndex |
      | edit              | 2           | 2             |
      | cancel editing of | 2           | 1             |

  @delete
  Scenario: Notes > Record View > Delete > Cancel/Confirm
    Given Notes records exist:
      | *name  | description        |
      | Note_1 | Note 1 description |

    When I choose Notes in modules menu
    When I select *Note_1 in #NotesList.ListView
    Then I should see #Note_1Record view
    When I open actions menu in #Note_1Record
    * I choose Delete from actions menu in #Note_1Record
    # Cancel record deletion
    When I Cancel confirmation alert
    Then I should see #Note_1Record view
    Then I verify fields on #Note_1Record.HeaderView
      | fieldName | value  |
      | name      | Note_1 |
    When I open actions menu in #Note_1Record
    * I choose Delete from actions menu in #Note_1Record
    # Confirm record deletion
    When I Confirm confirmation alert
    Then I should see #NotesList.ListView view
    Then I should not see *Note_1 in #NotesList.ListView

  @edit-save
  Scenario: Notes > Record View > Edit > Save
    Given Notes records exist:
      | *name  |
      | Note_1 |
    When I choose Notes in modules menu
    When I select *Note_1 in #NotesList.ListView
    Then I should see #Note_1Record view
    When I open actions menu in #Note_1Record
    When I click Edit button on #Note_1Record header
    Then I should see #Note_1Record view
    When I provide input for #Note_1Record.HeaderView view
      | name          |
      | Note_1_update |
    When I provide input for #Note_1Record.RecordView view
      | description        |
      | Note 1 description |
    When I click Save button on #Note_1Record header
    Then I verify fields on #Note_1Record.HeaderView
      | fieldName | value         |
      | name      | Note_1_update |
    Then I verify fields on #Note_1Record.RecordView
      | fieldName   | value              |
      | description | Note 1 description |

  @copy
  Scenario Outline: Notes > Record View > Copy > Save/Cancel
    Given Notes records exist:
      | *   | name  | description       |
      | N_1 | Note1 | Note1 description |

    # Copy (or cancel copy of) record in the record view
    When I <action> *N_1 record in Notes record view with the following header values:
      | *   | name              |
      | N_2 | Note<changeIndex> |

    # Verify if copy is (is not) created
    Then Notes *N_<expectedIndex> should have the following values:
      | fieldName   | value               |
      | name        | Note<expectedIndex> |
      | description | Note1 description   |

    Examples:
      | action         | changeIndex | expectedIndex |
      | cancel copy of | 2           | 1             |
      | copy           | 2           | 2             |

  @create
  Scenario: Notes > Create
    # Click Create Note in Mega menu
    When I choose Notes in modules menu
    When I click Create button on #NotesList header
    When I click show more button on #NotesDrawer view
    # Populate Header data
    When I provide input for #NotesDrawer.HeaderView view
      | *   | name  |
      | N_1 | Note1 |
    # Populate record data
    When I provide input for #NotesDrawer.RecordView view
      | *   | description       | portal_flag | tag   |
      | N_1 | Note1 description | True        | note1 |
    # Save
    When I click Save button on #NotesDrawer header
    When I close alert
    # Verify that record is created successfully
    Then Notes *N_1 should have the following values:
      | fieldName   | value             |
      | name        | Note1             |
      | portal_flag | true              |
      | description | Note1 description |
