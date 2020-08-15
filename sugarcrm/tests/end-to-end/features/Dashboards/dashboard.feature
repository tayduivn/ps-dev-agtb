# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@dashboard @dashlets @job8
Feature: Dashboard main functionality verification

  Background:
    Given I use default account
    Given I launch App

  @create_dashboard
  Scenario: List View > Create Dashboard > Cancel
    Given Accounts records exist:
      | *name |
      | A1    |
    Given I open about view and login
    When I choose Accounts in modules menu

    # Create new dashboard > Cancel
    When I cancel creation of new dashboard
      | *           | name          |
      | DashboardID | New Dashboard |

    # Verify no dashboard is created
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value                   |
      | name      | Accounts List Dashboard |

  @create_dashboard @add_dashlet @delete_dashboard @pr
  Scenario: List View > Create Dashboard > Add Dashlets > Save > Delete Dashboard
    Given Accounts records exist:
      | *name |
      | A1    |
    Given I open about view and login
    When I choose Accounts in modules menu

    # Create new dashboard > Save
    When I create new dashboard
      | *           | name          |
      | DashboardID | New Dashboard |

    # Add 3 Dashlets to the newly created dashboard
    When I add Top10Sales dashlet to #Dashboard
      | label        |
      | Top 10 Sales |

    And I add KBArticles dashlet to #Dashboard
      | label       |
      | KB Articles |

    And I add ListView dashlet to #Dashboard
      | label         | module   | limit |
      | Contacts List | Contacts | 10    |

    # Verify that new dashboard is created
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value         |
      | name      | New Dashboard |

    # Delete dashboard
    When I delete dashboard

    # Verify the dashboard is successfully deleted
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value                   |
      | name      | Accounts List Dashboard |

  @create_dashboard @add_dashlet
  Scenario: Record view View > Create Dashboard > Add Dashlets > Save
    Given Accounts records exist:
      | *name |
      | A1    |
    Given I open about view and login
    When I choose Accounts in modules menu

    # Navigate to record view
    When I select *A1 in #AccountsList.ListView

    # Create new dashboard > Save
    When I create new dashboard
      | *           | name                 |
      | DashboardID | RecordView Dashboard |

    # Add Product Catalog Dashlet
    When I add ProductCatalog dashlet to #Dashboard
      | label           |
      | Product Catalog |

    When I add ActiveTasks dashlet to #Dashboard
      | label           | visibility |
      | My Active Tasks | No         |

    # Verify that new dashboard is created
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value                |
      | name      | RecordView Dashboard |

    # Delete dashboard
    When I delete dashboard

    # Verify the dashboard is successfully deleted
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value                     |
      | name      | Accounts Record Dashboard |


  @homeDashboard_create  @add_dashlet
  Scenario: Home > Create Dashboard > Add Dashlets > Save > Delete Dashboard
    Given I open about view and login
    When I go to "Home" url

    # Create new dashboard > Save
    When I create new dashboard
      | *           | name          |
      | DashboardID | New Dashboard |

    # Add multiple dashlets to various columns of home dashboard
    When I add Top10Sales dashlet to #Dashboard
      | label         |
      | My Activities |

    And I add KBArticles dashlet to #Dashboard
      | label       |
      | KB Articles |

    And I add ListView dashlet to #Dashboard
      | label       | module   | limit |
      | KB Articles | Contacts | 10    |

    When I add History dashlet to #Dashboard
      | label          |
      | Recent History |

    # Verify that new dashboard is created
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value         |
      | name      | New Dashboard |

    # Delete home dashboard
    When I delete dashboard

     # Verify the dashboard is successfully deleted
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value          |
      | name      | Home Dashboard |
