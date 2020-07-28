# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_knowledgeBase @job8
Feature: Knowledge Base module verification

  Background:
    Given I use default account
    Given I launch App

  @list
  Scenario: Knowledge Base > List View > Review
    Given KBContents records exist:
      | *    | name      | kbdocument_body |
      | KB_1 | Article 1 | Hello World!    |
    Given I open KBContents view and login
    Then I should see *KB_1 in #KBContentsList.ListView
    Then I verify fields for *KB_1 in #KBContentsList.ListView
      | fieldName | value     |
      | name      | Article 1 |
    When I click on preview button on *KB_1 in #KBContentsList.ListView
    Then I should see #KB_1Preview view
    Then I click show more button on #KB_1Preview view
    Then I verify fields on #KB_1Preview.PreviewView
      | fieldName   | value     |
      | name        | Article 1 |
      | status      | Draft     |
      | language    | English   |
      | revision    | 1         |
      | is_external | false     |


  @list-search
  Scenario: Knowledge Base > List View > Filter
    Given KBContents records exist:
      | *    | name      | kbdocument_body |
      | KB_1 | Article 1 | Hello World!    |
      | KB_2 | Article 2 | Hello World!    |
      | KB_3 | Article 3 | Hello World!    |
    Given I open KBContents view and login
    When I search for "Article" in #KBContentsList.FilterView view
    Then I should see *KB_1 in #KBContentsList.ListView
    Then I should see *KB_2 in #KBContentsList.ListView
    Then I should see *KB_3 in #KBContentsList.ListView
    # Search for "KB_2" 1 record found
    When I search for "Article 2" in #KBContentsList.FilterView view
    Then I should not see *KB_1 in #KBContentsList.ListView
    Then I should see *KB_2 in #KBContentsList.ListView
    Then I should not see *KB_3 in #KBContentsList.ListView
    Then I verify fields for *KB_2 in #KBContentsList.ListView
      | fieldName | value     |
      | name      | Article 2 |
      | status    | Draft     |
      | language  | English   |


  @list-edit
  Scenario: Knowledge Base > List View > Inline Edit
    Given KBContents records exist:
      | *    | name      | kbdocument_body |
      | KB_1 | Article 1 | Hello World!    |
    Given I open KBContents view and login
    When I click on Edit button for *KB_1 in #KBContentsList.ListView
    When I set values for *KB_1 in #KBContentsList.ListView
      | fieldName | value       |
      | name      | KB_1 edited |
      | status    | In Review   |
    When I click on Cancel button for *KB_1 in #KBContentsList.ListView
    Then I verify fields for *KB_1 in #KBContentsList.ListView
      | fieldName | value     |
      | name      | Article 1 |
      | status    | Draft     |
    When I click on Edit button for *KB_1 in #KBContentsList.ListView
    When I set values for *KB_1 in #KBContentsList.ListView
      | fieldName | value       |
      | name      | KB_1 edited |
      | status    | In Review   |
    When I click on Save button for *KB_1 in #KBContentsList.ListView
    Then I verify fields for *KB_1 in #KBContentsList.ListView
      | fieldName | value       |
      | name      | KB_1 edited |
      | status    | In Review   |


  @list-delete
  Scenario: Knowledge Base > List View > Delete
    Given KBContents records exist:
      | *    | name      | kbdocument_body |
      | KB_1 | Article 1 | Hello World!    |
    Given I open KBContents view and login
    When I click on Delete button for *KB_1 in #KBContentsList.ListView
    When I Cancel confirmation alert
    Then I should see #KBContentsList view
    Then I should see *KB_1 in #KBContentsList.ListView
    When I click on Delete button for *KB_1 in #KBContentsList.ListView
    When I Confirm confirmation alert
    Then I should see #KBContentsList view
    Then I should not see *KB_1 in #KBContentsList.ListView


  @delete
  Scenario: Knowledge Base > Record View > Delete
    Given KBContents records exist:
      | *name | kbdocument_body |
      | KB_1  | Hello World!    |
    Given I open KBContents view and login
    When I select *KB_1 in #KBContentsList.ListView
    When I open actions menu in #KB_1Record
    When I choose Delete from actions menu in #KB_1Record
    # Cancel deletion
    When I Cancel confirmation alert
    Then I should see #KB_1Record view
    # Verify record is not deleted
    Then I should see #KB_1Record view
    Then I verify fields on #KB_1Record.HeaderView
      | fieldName | value |
      | name      | KB_1  |
    When I open actions menu in #KB_1Record
    # Confirm deletion
    * I choose Delete from actions menu in #KB_1Record
    When I Confirm confirmation alert
    Then I should see #KBContentsList.ListView view
    Then I should not see *KB_1 in #KBContentsList.ListView


  @copy_cancel
  Scenario:  Knowledge Base > Record View > Copy > Cancel
    Given KBContents records exist:
      | *name | kbdocument_body |
      | KB_1  | Hello World!    |
    Given I open KBContents view and login
    When I select *KB_1 in #KBContentsList.ListView
    When I open actions menu in #KB_1Record
    When I choose Copy from actions menu in #KB_1Record
    When I provide input for #KB_1Drawer.HeaderView view
      | name |
      | KB_2 |
    When I provide input for #KB_1Drawer.RecordView view
      | kbdocument_body     |
      | Hello World! (copy) |
    When I click Cancel button on #KBContentsDrawer header
    Then I verify fields on #KB_1Record.HeaderView
      | fieldName | value |
      | name      | KB_1  |
    Then I verify fields on #KB_1Record.RecordView
      | fieldName       | value        |
      | kbdocument_body | Hello World! |


  @copy_save
  Scenario:  Knowledge Base > Record View > Copy > Save
    Given KBContents records exist:
      | *name | kbdocument_body |
      | KB_1  | Hello World!    |
    Given I open KBContents view and login
    When I select *KB_1 in #KBContentsList.ListView
    When I open actions menu in #KB_1Record
    When I choose Copy from actions menu in #KB_1Record
    When I provide input for #KB_1Drawer.HeaderView view
      | name |
      | KB_2 |
    When I provide input for #KB_1Drawer.RecordView view
      | kbdocument_body     |
      | Hello World! (copy) |
    When I click Save button on #KBContentsDrawer header
    When I close alert
    Then I verify fields on #KB_1Record.HeaderView
      | fieldName | value |
      | name      | KB_2  |
    Then I verify fields on #KB_1Record.RecordView
      | fieldName       | value                                  |
      | kbdocument_body | <p>Hello World! (copy)Hello World!</p> |


  @create_cancel @create_save
  Scenario: Knowledge Base > Create > Cancel/Save
    Given Users records exist:
      | *      | status | user_name | user_hash | last_name | first_name | email              |
      | user_1 | Active | user_1    | LOGIN     | uLast_1   | uFirst_1   | user_1@example.org |
    And Cases records exist:
      | *name | priority | status   |
      | Case0 | P1       | Assigned |
    Given I open KBContents view and login
    # Cancel
    When I click Create button on #KBContentsList header
    When I provide input for #KBContentsDrawer.HeaderView view
      | *    | name | status    |
      | KB_1 | KB_1 | In Review |
    When I provide input for #KBContentsDrawer.RecordView view
      | *    | kbdocument_body |
      | KB_1 | New Article     |
    When I click Cancel button on #KBContentsDrawer header
    When I verify number of records in #KBContentsList.ListView is 0
    # Save
    When I click Create button on #KBContentsList header
    When I click show more button on #KBContentsDrawer view
    When I provide input for #KBContentsDrawer.HeaderView view
      | *    | name | status    |
      | KB_1 | KB_1 | In Review |
    When I provide input for #KBContentsDrawer.RecordView view
      | *    | kbdocument_body | tag          | active_date | exp_date   | kbsapprover_name | category_name |
      | KB_1 | New Article     | kb, article1 | 12/20/2020  | 12/20/2021 | uFirst_1 uLast_1 | Category 123  |
    When I click Save button on #KBContentsDrawer header
    When I close alert
    Then I should see *KB_1 in #KBContentsList.ListView
    When I click on preview button on *KB_1 in #KBContentsList.ListView
    Then I should see #KB_1Preview view
    Then I click show more button on #KB_1Preview view
    Then I verify fields on #KB_1Preview.PreviewView
      | fieldName        | value            |
      | name             | KB_1             |
      | status           | In Review        |
      | language         | English          |
      | revision         | 1                |
      | category_name    | Category 123     |
      | is_external      | false            |
      | kbsapprover_name | uFirst_1 uLast_1 |
      | exp_date         | 12/20/2021       |
      | active_date      | 12/20/2020       |
      | tag              | article1,kb      |


  @record_localization
  Scenario: Knowledge Base > Localization
    Given KBContents records exist:
      | *    | name      | kbdocument_body |
      | KB_1 | Article 1 | Hello World!    |
    Given I open KBContents view and login
    When I click on localization button for *KB_1 in #KBContentsList.ListView
    Then I check alert
      | type    | message                                                                                           |
      | warning | Unable to create a new localization as a localization version exists for all available languages. |
    When I close alert


  @record_revision
  Scenario: Knowledge Base > Revisions
    Given KBContents records exist:
      | *    | name      | kbdocument_body |
      | KB_1 | Article 1 | Hello World!    |
    Given I open KBContents view and login
    # Create revision
    When I click on revision button for *KB_1 in #KBContentsList.ListView
    When I provide input for #KB_1Drawer.HeaderView view
      | *       | name              | status    |
      | KB_rev1 | KB_1 - revision 1 | In Review |
    When I provide input for #KB_1Drawer.RecordView view
      | *       | kbdocument_body |
      | KB_rev1 | Revision 1      |
    When I click Save button on #KBContentsDrawer header
    When I close alert
    When I select *KB_rev1 in #KBContentsList.ListView
    Then I verify fields on #KB_rev1Record.HeaderView
      | fieldName | value             |
      | name      | KB_1 - revision 1 |
      | status    | In Review         |
    Then I verify fields on #KB_rev1Record.RecordView
      | fieldName       | value                         |
      | kbdocument_body | <p>Revision 1Hello World!</p> |
    # Check revision subpanels
    When I open the revisions subpanel on #KB_rev1Record view
    When I verify number of records in #KB_rev1Record.SubpanelsLayout.subpanels.revisions is 1
    # Verify dashlet appearance
    When I click useful button on #KB_rev1Record view
    # Then I verify that FirstDashlet element from #Dashboard.DashboardView still looks like useful1
    When I click notuseful button on #KB_rev1Record view
    # Then I verify that FirstDashlet element from #Dashboard.DashboardView still looks like useful2

  @view_categories
  Scenario: Knowledge Base > Create Category
    Given KBContents records exist:
      | *    | name      | kbdocument_body |
      | KB_1 | Article 1 | Hello World!    |
    Given I open KBContents view and login
    # Cancel Category Creation and CLose the draawer
    When I click on "View Categories" menu item
    When I click Close button on #KBViewCategoriesDrawer header
    When I should see #KBContentsList view
    # Create New Category
    When I click on "View Categories" menu item
    When I click CreateCategory button on #KBViewCategoriesDrawer header
    When I create new category for #KBViewCategoriesDrawer.KBCategoriesList view
      | *name |
      | KBC_1 |
    Then I should see *KBC_1 in #KBViewCategoriesDrawer.KBCategoriesList


  @list
  Scenario: Knowledge Base Templates > List View > Review
    Given KBContentTemplates records exist:
      | *     | name       | body          |
      | KBT_1 | Template 1 | My Template 1 |
    Given I open about view and login
    When I go to "KBContentTemplates" url
    Then I should see *KBT_1 in #KBContentTemplatesList.ListView
    Then I verify fields for *KBT_1 in #KBContentTemplatesList.ListView
      | fieldName | value      |
      | name      | Template 1 |
    When I click on preview button on *KBT_1 in #KBContentTemplatesList.ListView
     # TODO: This can be uncommented after AT-371 is fixed
#    Then I should see #KBT_1Preview view
#    Then I click show more button on #KBT_1Preview view
#    Then I verify fields on #KBT_1Preview.PreviewView
#      | fieldName | value         |
#      | name      | Template 1    |
#      | body      | My Template 1 |


  @kbTemplates_use_existing
  Scenario: Knowledge Base > Use Templates
    Given KBContentTemplates records exist:
      | *     | name       | body          |
      | KBT_1 | Template 1 | My Template 1 |
      | KBT_2 | Template 2 | My Template 2 |
    Given I open KBContents view and login
    When I click Create button on #KBContentsList header
    # Select existing template for a new article
    When I use existing template *KBT_1 for the new article on #KBContentsDrawer view
    Then I verify fields on #KBContentsDrawer.RecordView
      | fieldName       | value                |
      | kbdocument_body | <p>My Template 1</p> |
    # Change template for a new article
    When I use existing template *KBT_2 for the new article on #KBContentsDrawer view
    When I Confirm confirmation alert
    Then I verify fields on #KBContentsDrawer.RecordView
      | fieldName       | value                |
      | kbdocument_body | <p>My Template 2</p> |
    When I provide input for #KBContentsDrawer.HeaderView view
      | *    | name | status    |
      | KB_1 | KB_1 | In Review |
    When I click Save button on #KBContentsDrawer header
    When I close alert
    Then I should see *KB_1 in #KBContentsList.ListView
    When I select *KB_1 in #KBContentsList.ListView
    When I click show more button on #KB_1Record view
    Then I verify fields on #KB_1Record.HeaderView
      | fieldName | value     |
      | name      | KB_1      |
      | status    | In Review |
    Then I verify fields on #KB_1Record.RecordView
      | fieldName       | value         |
      | kbdocument_body | My Template 2 |
      | language        | English       |
      | revision        | 1             |


  @kbTemplates_createNew
  Scenario: Knowledge Base > Create New Template on the fly
    Given I open KBContents view and login
    When I click Create button on #KBContentsList header
    # Cancel from Search and Select drawer
    When I click Templates button on #KBContentsDrawer.RecordView
    When I click Close button on #KBContentsSearchAndSelect header
    # Create new template from Search and Select drawer
    When I click Templates button on #KBContentsDrawer.RecordView
    When I click Create button on #KBContentsSearchAndSelect header
    When I provide input for #KBContentsDrawer.HeaderView view
      | *   | name         |
      | T_1 | New Template |
    When I provide input for #KBContentsDrawer.RecordView view
      | *   | body         |
      | T_1 | New Template |
    When I click Save button on #KBContentsDrawer header
    When I close alert
    # Verify that article body is populated from the newly created template
    Then I verify fields on #KBContentsDrawer.RecordView
      | fieldName       | value               |
      | kbdocument_body | <p>New Template</p> |
    When I click show more button on #KBContentsDrawer view
    When I provide input for #KBContentsDrawer.HeaderView view
      | *    | name | status   |
      | KB_1 | KB_1 | Approved |
    When I provide input for #KBContentsDrawer.RecordView view
      | *    | active_date | exp_date   |
      | KB_1 | 12/20/2020  | 12/20/2021 |
    When I click Save button on #KBContentsDrawer header
    When I close alert
    When I select *T_1 in #KBContentsList.ListView
    When I click show more button on #T_1Record view
    Then I verify fields on #T_1Record.HeaderView
      | fieldName | value    |
      | name      | KB_1     |
      | status    | Approved |
    Then I verify fields on #T_1Record.RecordView
      | fieldName       | value               |
      | kbdocument_body | <p>New Template</p> |
      | language        | English             |
      | revision        | 1                   |


  @kbSettings @ci-excluded
  Scenario: Knowledge Base > Settings
    Given KBContents records exist:
      | *    | name      | kbdocument_body |
      | KB_1 | Article 1 | Hello World!    |
    Given I open KBContents view and login
  # Select Settings in KB menga menu
    When I click on "Settings" menu item
    When I add a new language on #KBSettingsDrawer
      | language_code | language_label | primary |
      | PO            | Polish         | false   |
    When I click on "View Articles" menu item
    When I click Create button on #KBContentsList header
    When I click show more button on #KBContentsDrawer view
    When I provide input for #KBContentsDrawer.HeaderView view
      | *name | status    |
      | KB_1  | In Review |
    When I provide input for #KBContentsDrawer.RecordView view
      | *    | kbdocument_body | language |
      | KB_1 | New Article     | Russian  |
    When I click Save button on #KBContentsDrawer header
    When I close alert


  @edit_categories
  Scenario: Knowledge Base > Categories List view > Edit/Move/Delete
    Given KBContents records exist:
      | *    | name      | kbdocument_body |
      | KB_1 | Article 1 | Hello World!    |
    Given I open KBContents view and login
    # Create one new category
    When I click on "View Categories" menu item
    When I click CreateCategory button on #KBViewCategoriesDrawer header
    When I create new category for #KBViewCategoriesDrawer.KBCategoriesList view
      | *name        |
      | KBCategory_1 |
    Then I should see *KBCategory_1 in #KBViewCategoriesDrawer.KBCategoriesList
    # Create another new category
    When I click CreateCategory button on #KBViewCategoriesDrawer header
    When I create new category for #KBViewCategoriesDrawer.KBCategoriesList view
      | *name        |
      | KBCategory_2 |
#    Then I should see *KBCategory_2 in #KBViewCategoriesDrawer.KBCategoriesList
    Then I verify number of records in #KBViewCategoriesDrawer.KBCategoriesList is 2
    When I edit *KBCategory_1 on #KBViewCategoriesDrawer.KBCategoriesList view
      | name          |
      | KBCategory_1a |

    When I moveDown *KBCategory_1 on #KBViewCategoriesDrawer.KBCategoriesList view
    When I wait for 2 seconds
#    When I moveDown *KBCategory_2 on #KBViewCategoriesDrawer.KBCategoriesList view
#    When I wait for 2 seconds
