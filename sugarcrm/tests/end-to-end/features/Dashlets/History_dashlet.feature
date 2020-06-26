# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@dashboard @dashlets @job5 @ci-excluded
# Temporarily disable this test until new Dashboard behavior is complete
Feature: History dashlet verification

  Background:
    Given I am logged in

  Scenario: History dashlet verification

    # Create 3 meeting records that held in last 7 days
    Given 3 Meetings records exist:
      | *          | name              | status | date_start      | assigned_user_id |
      | T{{index}} | Meeting {{index}} | Held   | now -{{index}}d | 1                |

    # # Create 2 meeting records in the past
    Given 1 Meetings records exist:
      | *  | name      | status | date_start | assigned_user_id |
      | T4 | Meeting 4 | Held   | now -10d   | 1                |
      | T5 | Meeting 5 | Held   | now -60d   | 1                |

    # Create 3 call records that held in last 7 days
    Given 3 Calls records exist:
      | *          | name           | status | date_start      | duration_hours | duration_minutes | assigned_user_id |
      | C{{index}} | Call {{index}} | Held   | now -{{index}}d | 0              | 30               | 1                |

    # Create 2 call records that held in the past
    Given 1 Calls records exist:
      | *  | name   | status | date_start | duration_hours | duration_minutes | assigned_user_id |
      | C4 | Call 4 | Held   | now -20d   | 0              | 30               | 1                |
      | C5 | Call 5 | Held   | now -80d   | 0              | 30               | 1                |

    # Add New User
    Given Users records exist:
      | *      | status | user_name | user_hash | last_name | first_name | email              |
      | user_1 | Active | user_1    | LOGIN     | uLast     | uFirst     | user_1@example.org |

    # Go to Home page
    When I go to "Home" url

    # Create new dashboard > Save
    When I create new dashboard with two column layout
      | *   | name        |
      | D_1 | Dashboard 1 |

    # Add multiple dashlets to various columns of home dashboard
    When I add History dashlet to #Dashboard at column 1
      | label   |
      | History |

    # When I select last 7 days
    When I select "Last 7 Days" in #Dashboard.HistoryDashlet
    # Verify number of records displayed on the Meetings tab
    Then I verify the record count in Meetings tab is equal to 3 in #Dashboard.HistoryDashlet
    # Navigate to Calls tab
    When I navigate to Calls tab in #Dashboard.HistoryDashlet
    # Verify number of records displayed on the Calls tab
    Then I verify the record count in Calls tab is equal to 3 in #Dashboard.HistoryDashlet

    # When I select last 30 days
    When I select "Last 30 Days" in #Dashboard.HistoryDashlet
    # Verify number of records displayed on the Calls tab
    Then I verify the record count in Calls tab is equal to 4 in #Dashboard.HistoryDashlet
    # Navigate to Meetings tab
    When I navigate to Meetings tab in #Dashboard.HistoryDashlet
    # Verify number of records displayed on the Meetings tab
    Then I verify the record count in Meetings tab is equal to 4 in #Dashboard.HistoryDashlet

    # When I select last quarter
    When I select "Last Quarter" in #Dashboard.HistoryDashlet
    # Verify number of records displayed on the Meetings tab
    Then I verify the record count in Meetings tab is equal to 5 in #Dashboard.HistoryDashlet
    # Navigate to Calls tab
    When I navigate to Calls tab in #Dashboard.HistoryDashlet
    # Verify number of records displayed on the Calls tab
    Then I verify the record count in Calls tab is equal to 5 in #Dashboard.HistoryDashlet

    # Create Calls record with Held status
    Given Calls records exist:
      | *  | name    | status | assigned_user_id | date_start | duration_hours | duration_minutes |
      | C6 | Calls 6 | Held   | 1                | now -20d   | 0              | 30               |

    # Create Meetings record with Held status
    Given Meetings records exist:
      | *  | name      | status | assigned_user_id | date_start |
      | T6 | Meeting 6 | Held   | 1                | now -20d   |

    # Change 'assigned to' field to new user for created Call and Meeting
    And I perform mass update of Calls [*C6] with the following values:
      | fieldName          | value        |
      | assigned_user_name | uFirst uLast |

    And I perform mass update of Meetings [*T6] with the following values:
      | fieldName          | value        |
      | assigned_user_name | uFirst uLast |

    When I choose Home in modules menu

    # Set visibility to 'group'
    When I set visibility as 'group' in #Dashboard.HistoryDashlet
    # When I select last quarter
    When I select "Last Quarter" in #Dashboard.HistoryDashlet
    # Verify number of records displayed on Calls tab
    Then I verify the record count in Calls tab is equal to 6 in #Dashboard.HistoryDashlet
    # Verify number of records displayed on Meetings tab
    Then I verify the record count in Meetings tab is equal to 6 in #Dashboard.HistoryDashlet

    # Click on the configure setting and select edit to update the dashlet setting
    When I edit dashlet settings of #Dashboard.HistoryDashlet with the following values:
      | label          | limit |
      | History Update | 5     |
    And I close alert

    # When I select last quarter
    When I select "Last Quarter" in #Dashboard.HistoryDashlet

    # Verify number of records displayed on Meetings tab
    Then I verify the record count in Meetings tab is equal to 5+ in #Dashboard.HistoryDashlet

    # Click on the 'More meetings' link
    When I display more records in #Dashboard.HistoryDashlet view

    # Verify number of records displayed on Meetings tab
    Then I verify the record count in Meetings tab is equal to 6 in #Dashboard.HistoryDashlet

    # Verify that dashelt title is updated correctly
    Then I verify 'History Update' title updated in #Dashboard.HistoryDashlet

    # Navigate to 'Emails' tab
    When I navigate to Emails tab in #Dashboard.HistoryDashlet
    # Verify 'No data available' message is displayed
    Then I verify 'No data available.' message appears in #Dashboard.HistoryDashlet

    # When I create Archived Email in history dashlet
    When I Create Archived Email in #Dashboard.HistoryDashlet
    When I provide input for #EmailsDrawer.RecordView view
      | *   | name                  | date_sent          | from_collection |
      | E_1 | Test Archived Email 1 | 05/01/2018-12:00pm | uLast           |
    When I click Save button on #EmailsDrawer header
    When I close alert

    # Verify number of records displayed on Emails tab
    Then I verify the record count in Emails tab is equal to 1 in #Dashboard.HistoryDashlet
