@dashboard @dashlets
Feature: Dashboard main functionality verification

  Background:
    Given I use default account
    Given I launch App

  @create_dashboard @delete_dashboard @add_dashlet @pr
  Scenario: Create Dashboard > Add Dashlets > Cancel/Save/Delete
    Given Accounts records exist:
      | *name | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | A1    | Cupertino            | 10050 N. Wolfe Rd      | 95014                      | CA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu

    # Create new dashboard > Cancel
    When I click Create button on #Dashboard header
    When I provide input for #Dashboard.HeaderView view
      | *           | name          |
      | DashboardID | New Dashboard |
    When I click Cancel button on #Dashboard header

    # Verify no dashboard is created
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value                   |
      | name      | Accounts List Dashboard |

    # Create new dashboard > Save
    When I click Create button on #Dashboard header
    When I provide input for #Dashboard.HeaderView view
      | *           | name          |
      | DashboardID | New Dashboard |

    # Add 3 Dashlets
    When I add MyActivityStream dashlet to #Dashboard
      | label         |
      | My Activities |

    When I add KBArticles dashlet to #Dashboard
      | label       |
      | KB Articles |

    When I add ListView dashlet to #Dashboard
      | label       | module   | limit |
      | KB Articles | Contacts | 10    |

    # Save new Dashboard
    When I click Save button on #Dashboard header
    When I close alert

    # Verify that new dashboard is created
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value         |
      | name      | New Dashboard |

    # Delete dashboard
    When I open actions menu in #Dashboard
    And I choose Edit from actions menu in #Dashboard
    And I choose Delete from actions menu in #Dashboard
    And I Confirm confirmation alert
    And I close alert

    # Verify the dashboard is successfully deleted
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value                   |
      | name      | Accounts List Dashboard |

    # Go to record view
    When I select *A1 in #AccountsList.ListView
    # Create new dashboard > Save
    When I click Create button on #Dashboard header
    When I provide input for #Dashboard.HeaderView view
      | *           | name                 |
      | DashboardID | RecordView Dashboard |

    # Add Product Catalog Dashlet
    When I add ProductCatalog dashlet to #Dashboard
      | label           |
      | Product Catalog |

    # Save Dashboard
    When I click Save button on #Dashboard header
    When I close alert

    # Verify that new dashboard is created
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value                |
      | name      | RecordView Dashboard |

    # Delete dashboard
    When I open actions menu in #Dashboard
    And I choose Edit from actions menu in #Dashboard
    And I choose Delete from actions menu in #Dashboard
    And I Confirm confirmation alert
    And I close alert

    # Verify the dashboard is successfully deleted
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value                     |
      | name      | Accounts Record Dashboard |
