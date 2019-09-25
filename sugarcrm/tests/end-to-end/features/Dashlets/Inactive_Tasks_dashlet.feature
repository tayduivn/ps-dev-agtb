# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@dashboard @dashlets @job5
Feature: Inactive Tasks dashlet verification

  Background:
    Given I am logged in

  @inactive_tasks_dashlet
  Scenario: Inactive Tasks dashlet verification

    # Create 'Deferred' tasks records
    Given 6 Tasks records exist:
      | *          | name           | status   | assigned_user_id |
      | T{{index}} | Task {{index}} | Deferred | 1                |

    # Add New User
    Given Users records exist:
      | *      | status | user_name | user_hash | last_name | first_name | email              |
      | user_1 | Active | user_1    | LOGIN     | uLast     | uFirst     | user_1@example.org |

    # Go to Home page
    And I go to "Home" url

    # Create new dashboard > Save
    When I create new dashboard with two column layout
      | *   | name        |
      | D_1 | Dashboard 1 |

    # Add multiple dashlets to various columns of home dashboard
    When I add InactiveTasks dashlet to #Dashboard at column 1
      | label          |
      | Inactive Tasks |

    # Create 'Deferred' task record
    When I Create Task in #Dashboard.InactiveTasksDashlet
    When I provide input for #TasksDrawer.HeaderView view
      | *   | name              |
      | T_1 | Finalize the sale |
    When I provide input for #TasksDrawer.RecordView view
      | *   | status   |
      | T_1 | Deferred |
    When I click Save button on #TasksDrawer header
    When I close alert

    # Verify number of records displayed on the "Deferred" tab
    Then I verify the record count in Deferred tab is equal to 7 in #Dashboard.InactiveTasksDashlet

    # Verify record is present in 'Deferred' tab
    Then I verify *T1 record info in #Dashboard.InactiveTasksDashlet.ActivitiesList
      | fieldName | value   |
      | name      | Task 1  |

    # Verify record is present in 'Deferred' tab
    Then I verify *T2 record info in #Dashboard.InactiveTasksDashlet.ActivitiesList
      | fieldName | value   |
      | name      | Task 2  |

    # Verify record is present in 'Deferred' tab
    Then I verify *T_1 record info in #Dashboard.InactiveTasksDashlet.ActivitiesList
      | fieldName | value             |
      | name      | Finalize the sale |

    # Navigate to 'Completed' tab
    When I navigate to Completed tab in #Dashboard.InactiveTasksDashlet

    # Verify 'No data available' message is displayed
    Then I verify 'No data available.' message appears in #Dashboard.InactiveTasksDashlet

    # Create 'Completed' task record
    When I Create Task in #Dashboard.InactiveTasksDashlet
    When I provide input for #TasksDrawer.HeaderView view
      | *   | name          |
      | T_2 | Send contract |
    When I provide input for #TasksDrawer.RecordView view
      | *   | status   |
      | T_2 | Completed|
    When I click Save button on #TasksDrawer header
    When I close alert

    # Verify number of records on the tab
    Then I verify the record count in Completed tab is equal to 1 in #Dashboard.InactiveTasksDashlet

    # Create Task record with Deferred status
    Given Tasks records exist:
      | *      | name           | status   | assigned_user_id |
      | Today1 | Today's Task 1 | Deferred | 1                |

    # Create Task record with Completed status
    Given Tasks records exist:
      | *      | name           | status    | assigned_user_id |
      | Today2 | Today's Task 2 | Completed | 1                |

    # Change 'assigned to' field to new user for created tasks
    And I perform mass update of Tasks [*Today1, *Today2] with the following values:
      | fieldName          | value        |
      | assigned_user_name | uFirst uLast |

    When I choose Home in modules menu

    # Set visibility to 'group'
    When I set visibility as 'group' in #Dashboard.InactiveTasksDashlet
    # Verify number of records displayed on Deferred tab
    Then I verify the record count in Deferred tab is equal to 8 in #Dashboard.InactiveTasksDashlet
    # Verify number of records displayed on Completed tab
    Then I verify the record count in Completed tab is equal to 2 in #Dashboard.InactiveTasksDashlet

    # Click on the configure setting and select edit to update the dashlet setting
    When I edit dashlet settings of #Dashboard.InactiveTasksDashlet with the following values:
      | label                 | limit |
      | Inactive Tasks Update | 5     |

    # Verify number of records displayed on Deferred tab
    Then I verify the record count in Deferred tab is equal to 5+ in #Dashboard.InactiveTasksDashlet

    # Click on the 'More tasks' link
    When I display more records in #Dashboard.InactiveTasksDashlet view

    # Verify number of records displayed on Deferred tab
    Then I verify the record count in Deferred tab is equal to 8 in #Dashboard.InactiveTasksDashlet

    # Set visibility to 'user'
    When I set visibility as 'user' in #Dashboard.InactiveTasksDashlet
    When I navigate to Deferred tab in #Dashboard.InactiveTasksDashlet
    When I select *T4 in #Dashboard.InactiveTasksDashlet.ActivitiesList
    Then I should see #T4Record view
