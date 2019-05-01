# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_tasks @job3 @xxx
Feature: Tasks module verification

  Background:
    Given I am logged in

  @list
  Scenario: Tasks > List View > Preview
    Given Tasks records exist:
      | *  | name   | status      | priority | date_start          | date_due            | description               |
      | T1 | Task 1 | Not Started | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Seedbed testing for Tasks |

    When I choose Tasks in modules menu
    Then I should see *T1 in #TasksList.ListView

    Then Tasks *T1 should have the following values in the preview:
      | fieldName   | value                     |
      | name        | Task 1                    |
      | status      | Not Started               |
      | priority    | High                      |
      | description | Seedbed testing for Tasks |
      | date_start  | 04/16/2020 02:30pm        |
      | date_due    | 04/18/2020 02:30pm        |


  @list-edit
  Scenario Outline: Tasks > List View > Inline Edit > Cancel/Save

    Given Tasks records exist:
      | *  | name   | status      | priority | date_start          | date_due            | description               |
      | T1 | Task 1 | Not Started | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Seedbed testing for Tasks |

    # Edit (or cancel editing of) record in the list view
    When I <action> *T1 record in Tasks list view with the following values:
      | fieldName | value              |
      | name      | Task <changeIndex> |

    # Verify if edit (or cancel) is successful
    Then Tasks *T1 should have the following values in the list view:
      | fieldName | value                |
      | name      | Task <expectedIndex> |

    Examples:
      | action            | changeIndex | expectedIndex |
      | edit              | 2           | 2             |
      | cancel editing of | 2           | 1             |


  @list-delete
  Scenario Outline: Tasks > List View > Delete

    Given Tasks records exist:
    | *  | name   | status      | priority | date_start          | date_due            | description               |
    | T1 | Task 1 | Not Started | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Seedbed testing for Tasks |

    # Delete (or Cancel deletion of) record from list view
    When I <action> *T1 record in Tasks list view

    # Verify that record is (is not) deleted
    Then I <expected> see [*T1] on Tasks list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |


  @delete
  Scenario Outline: Tasks >  Record View > Delete

    Given Tasks records exist:
      | *  | name   | status      | priority | date_start          | date_due            | description               |
      | T1 | Task 1 | Not Started | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Seedbed testing for Tasks |

    # Delete (or Cancel deletion of) record in the record view
    When I <action> *T1 record in Tasks record view

    # Verify that record is (is not) deleted
    When I choose Tasks in modules menu
    Then I <expected> see [*T1] on Tasks list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |


  @close
  Scenario Outline: Tasks >  Record View > Close

    Given Tasks records exist:
      | *  | name   | status      | priority | date_start          | date_due            | description               |
      | T1 | Task 1 | Not Started | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Seedbed testing for Tasks |

    # Close record in the record view
    When I <action> *T1 record in Tasks record view

    # Verify if close is successful
    Then Tasks *T1 should have the following values:
      | fieldName | value     |
      | status    | Completed |

    Examples:
      | action |
      | close  |


  @copy
  Scenario Outline: Tasks > Record view > Copy > Cancel/Save

    Given Tasks records exist:
      | *  | name   | status      | priority | date_start          | date_due            | description               |
      | T1 | Task 1 | Not Started | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Seedbed testing for Tasks |

    # Copy (or cancel copy of) record in the record view
    When I <action> *T1 record in Tasks record view with the following header values:
      | *  | name               |
      | T2 | Task <changeIndex> |

    # Verify if copy is (is not) created
    Then Tasks *T<expectedIndex> should have the following values:
      | fieldName | value                |
      | name      | Task <expectedIndex> |

    When I choose Tasks in modules menu
    Then I should see *T<expectedIndex> in #TasksList.ListView
    Then I verify number of records in #TasksList.ListView is <numberRecord>

    Examples:
      | action         | changeIndex | expectedIndex | numberRecord |
      | cancel copy of | 2           | 1             | 1            |
      | copy           | 2           | 2             | 2            |


  @create_new_task
  Scenario: Tasks > Create > Cancel/Save

   # Click Create Task in Mega menu
    When I choose Tasks in modules menu and select "Create Task" menu item
    When I click show more button on #TasksDrawer view

    # Populate Header data
    When I provide input for #TasksDrawer.HeaderView view
      | *  | name     |
      | T1 | New Task |

    # Populate record data
    When I provide input for #TasksDrawer.RecordView view
      | *  | date_start         | date_due   | status      | priority | description               |
      | T1 | 05/01/2020-12:00pm | 05/10/2020 | Not Started | High     | Seedbed testing for Tasks |

    # Save
    When I click Save button on #TasksDrawer header
    When I close alert

    # Verify that record is created successfully
    Then Tasks *T1 should have the following values:
      | fieldName   | value                     |
      | name        | New Task                  |
      | status      | Not Started               |
      | priority    | High                      |
      | date_start  | 05/01/2020 12:00pm        |
      | description | Seedbed testing for Tasks |

