# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_cases @job3
Feature: Cases module verification

  Background:
    Given I am logged in

  @list
  Scenario Outline: Cases > List View > Contain pre-created record
    Given Cases records exist:
      | *  | name    |
      | C1 | <name1> |

    When I choose Cases in modules menu
    Then I should see *C1 in #CasesList.ListView

    Examples:
      | name1  |
      | Case 1 |

  @list-edit
  Scenario Outline: Cases > List View > Inline Edit > Cancel/Save

    Given Accounts records exist:
      | *  | name      |
      | A1 | Account 1 |
      | A2 | Account 2 |

    And Cases records exist:
      | *  | name    | priority    | status    |
      | C1 | <name1> | <priority1> | <status1> |

    When I choose Cases in modules menu
    Then I should see *C1 in #CasesList.ListView

    # Edit cases > Save > Verify
    When I click on Edit button for *C1 in #CasesList.ListView
    When I set values for *C1 in #CasesList.ListView
      | fieldName    | value     |
      | name         | Case B    |
      | account_name | Account 2 |
      | priority     | Low       |
      | status       | Rejected  |
    When I click on Save button for *C1 in #CasesList.ListView
    When I close alert
    Then I verify fields for *C1 in #CasesList.ListView
      | fieldName    | value     |
      | name         | Case B    |
      | account_name | Account 2 |
      | priority     | Low       |
      | status       | Rejected  |

    # Edit cases > Cancel > Verify
    When I click on Edit button for *C1 in #CasesList.ListView
    When I set values for *C1 in #CasesList.ListView
      | fieldName    | value     |
      | name         | Case A    |
      | account_name | Account 1 |
      | priority     | Medium    |
      | status       | Assigned  |
    When I click on Cancel button for *C1 in #CasesList.ListView
    Then I verify fields for *C1 in #CasesList.ListView
      | fieldName    | value     |
      | name         | Case B    |
      | account_name | Account 2 |
      | priority     | Low       |
      | status       | Rejected  |

    Examples:
      | name1  | priority1 | status1 |
      | Case 1 | High      | New     |

  @list-delete
  Scenario Outline: Cases > List View > Delete

    Given Cases records exist:
      | *  | name    | account1   | priority    | status    |
      | C1 | <name1> | <account1> | <priority1> | <status1> |

    When I choose Cases in modules menu
    Then I should see *C1 in #CasesList.ListView
    When I click on Delete button for *C1 in #CasesList.ListView
    When I Cancel confirmation alert
    Then I should see *C1 in #CasesList.ListView
    When I click on Delete button for *C1 in #CasesList.ListView
    When I Confirm confirmation alert
    Then I should see #CasesList view
    Then I should not see *C1 in #CasesList.ListView

    Examples:
      | name1  | account1  | priority1 | status1 |
      | Case 1 | Account 1 | High      | New     |

  @delete
  Scenario Outline: Cases >  Record View > Delete
    Given Cases records exist:
      | *  | name    |
      | C1 | <name1> |

    When I choose Cases in modules menu
    Then I should see *C1 in #CasesList.ListView
    When I select *C1 in #CasesList.ListView
    Then I should see #C1Record view
    When I open actions menu in #C1Record
    And I choose Delete from actions menu in #C1Record
    When I Cancel confirmation alert
    Then I should see #C1Record view
    Then I verify fields on #C1Record.HeaderView
      | fieldName | value   |
      | name      | <name1> |

    When I open actions menu in #C1Record
    And I choose Delete from actions menu in #C1Record
    When I Confirm confirmation alert
    Then I should see #CasesList.ListView view
    Then I should not see *C1 in #CasesList.ListView

    Examples:
      | name1  |
      | Case 1 |

  @copy
  Scenario Outline: Cases > Record view > Copy > Cancel/Save
    Given Cases records exist:
      | *  | name    |
      | C1 | <name1> |
    And Accounts records exist:
      | *  | name       |
      | A1 | <account1> |

    When I choose Cases in modules menu
    Then I should see *C1 in #CasesList.ListView
    When I select *C1 in #CasesList.ListView
    Then I should see #C1Record view

  # Copy Cases > Cancel
    When I open actions menu in #C1Record
    When I choose Copy from actions menu in #C1Record
    When I click Cancel button on #CasesDrawer header
    Then I verify fields on #C1Record.HeaderView
      | fieldName | value   |
      | name      | <name1> |

  # Copy Cases > Save
    When I open actions menu in #C1Record
    When I choose Copy from actions menu in #C1Record
    When I provide input for #CasesDrawer.HeaderView view
      | name   |
      | Case 2 |
    When I provide input for #CasesDrawer.RecordView view
      | account_name | priority    | status    | type    | source    | description    | commentlog    |
      | <account1>   | <priority1> | <status1> | <type1> | <source1> | <description1> | <commentlog1> |
    When I click Save button on #CasesDrawer header
    When I close alert
    Then I verify fields on #C1Record.HeaderView
      | fieldName | value  |
      | name      | Case 2 |

    Examples:
      | name1  | account1  | priority1 | status1 | type1          | source1  | description1      | commentlog1 |
      | Case 1 | Account 1 | High      | New     | Administration | Internal | Cases Description | Comment Log |

  @create_article
  Scenario Outline: Cases > Record view > Create Article
    Given Cases records exist:
      | *  | name    |
      | C1 | <name1> |
    And Accounts records exist:
      | *  | name       |
      | A1 | <account1> |

    When I choose Cases in modules menu
    Then I should see *C1 in #CasesList.ListView
    When I select *C1 in #CasesList.ListView
    Then I should see #C1Record view

    # Copy Cases > Create Article
    When I open actions menu in #C1Record
    When I choose CreateArticle from actions menu in #C1Record
    When I provide input for #KBContentsDrawer.HeaderView view
      | *    | name | status    |
      | KB_1 | KB_1 | In Review |
    When I click Save button on #KBContentsDrawer header
    And I close alert
    Then I should see #C1Record view

    When I open the kbcontents subpanel on #C1Record view
    Then I verify fields for *KB_1 in #C1Record.SubpanelsLayout.subpanels.kbcontents
      | fieldName | value |
      | name      | KB_1  |

    Then KBContents *KB_1 should have the following values:
      | fieldName       | value                                                                  |
      | kbdocument_body | <p>Cases Number: {*C1.case_number}</p>    <p>Subject: <name1></p> |

    Examples:
      | name1  | account1  |
      | Case 1 | Account 1 |

  @create_new_case
  Scenario Outline: Cases > Create > Cancel/Save

    Given Accounts records exist:
      | *  | name       |
      | A1 | <account1> |

    When I choose Cases in modules menu
    # Create Cases > Cancel
    When I click Create button on #CasesList header
    When I provide input for #CasesDrawer.HeaderView view
      | *  | name    |
      | C1 | <name1> |
    When I provide input for #CasesDrawer.RecordView view
      | account_name | priority    | status    | type    | source    | description    | commentlog    |
      | <account1>   | <priority1> | <status1> | <type1> | <source1> | <description1> | <commentlog1> |
    When I click Cancel button on #CasesDrawer header
    Then I should see #CasesList.ListView view

    # Create Cases > Save
    When I click Create button on #CasesList header
    When I provide input for #CasesDrawer.HeaderView view
      | *  | name    |
      | C1 | <name1> |
    When I provide input for #CasesDrawer.RecordView view
      | account_name | priority    | status    | type    | source    | description    | commentlog    |
      | <account1>   | <priority1> | <status1> | <type1> | <source1> | <description1> | <commentlog1> |
    When I click Save button on #CasesDrawer header
    When I close alert
    Then I should see #CasesList.ListView view
    Then I should see *C1 in #CasesList.ListView
    When I click on preview button on *C1 in #CasesList.ListView
    Then I should see #C1Preview view
    Then I verify fields on #C1Preview.PreviewView
      | fieldName    | value          |
      | name         | <name1>        |
      | account_name | <account1>     |
      | priority     | <priority1>    |
      | status       | <status1>      |
      | type         | <type1>        |
      | source       | <source1>      |
      | description  | <description1> |

    Examples:
      | name1  | account1  | priority1 | status1 | type1          | source1  | description1      | commentlog1 |
      | Case 1 | Account 1 | High      | New     | Administration | Internal | Cases Description | Comment Log |