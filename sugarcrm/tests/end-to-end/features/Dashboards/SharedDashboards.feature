# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@dashboard @dashlets @job4
Feature: Shareable Dashboards functionality verification

  Background:
    Given I use default account
    Given I launch App

  @pr @ci-excluded
  Scenario Outline: Home > Shared dashboard

    # Create custom user
    Given I create custom user "user"
    And I open about view and login
    And I go to "Home" url

    # Create new dashboard > Save
    When I create new dashboard with three column layout
      | *   | name            |
      | D_1 | <dashboardName> |

    # Add multiple dashlets to various columns of home dashboard
    When I add MyActivityStream dashlet to #Dashboard at column 1
      | label         |
      | My Activities |

    And I add KBArticles dashlet to #Dashboard at column 2
      | label       |
      | KB Articles |

    And I add ListView dashlet to #Dashboard at column 3
      | label       | module   | limit |
      | KB Articles | Contacts | 10    |

    And I add History dashlet to #Dashboard at column 1
      | label          |
      | Recent History |

    # Verify that new dashboard is created
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value           |
      | name      | <dashboardName> |

    # Share Dashboard
    When I go to "Dashboards" url
    And I filter for the Dashboards record *D_1 named "<dashboardName>"

    # Open record in the record view
    When I select *D_1 in #DashboardsList.ListView

    # Edit dashboard: 1. add user's team. 2. Make dashboard default
    When I click Edit button on #DashboardsRecord header
    And I provide input for #DashboardsRecord.RecordView view
      | team_name           | default_dashboard |
      | add: user userLName | true              |
    And I click Save button on #DashboardsRecord header
    And I close alert

    # Logout from Admin and Login as another user
    When I go to "logout" url
    When I use account "user"
    When I open Dashboards view and login

    # Mark shared dashboard as favorite
    When I toggle favorite for *D_1 in #DashboardsList.ListView

    When I go to "Home" url
#    Then I verify that HomeDashboard element from #Dashboard.DashboardView still looks like HomeDashboard

    Examples:
      | dashboardName    |
      | Shared Dashboard |
