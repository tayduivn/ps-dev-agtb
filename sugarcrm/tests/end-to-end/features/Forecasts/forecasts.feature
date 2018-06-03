# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_contracts
Feature: Forecasts Sales Rep worksheet verification

  Background:
    Given I use default account
    Given I launch App

  @forecasts_sales_rep @pr
  Scenario: Forecasts > Sales Rep Worksheet

    Given I open about view and login

    # Configure Forecast module - Forecasts module must be configured before creating RLI records
    When I configure Forecasts module
      | name                           | value |
      | show_binary_ranges.include.min | 60    |
      | show_binary_ranges.include.max | 100   |
      | show_binary_ranges.exclude.min | 0     |
      | show_binary_ranges.exclude.max | 59    |

    # Create required records
    Given Accounts records exist:
      | *name |
      | Acc_1 |
    When I choose Opportunities in modules menu
    When I click Create button on #OpportunitiesList header
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name                  |
      | Opp_1 | CreateOpportunityTest |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | account_name |
      | Opp_1 | Acc_1        |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | *name | date_closed | best_case | sales_stage        | quantity | likely_case |
      | RLI_1 | 10/01/2020  | 150       | Negotiation/Review | 1        | 100         |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    When I provide input for #OpportunityDrawer.RLITable view for 2 row
      | *name | date_closed | best_case | sales_stage        | quantity | likely_case |
      | RLI_2 | 11/01/2020  | 250       | Negotiation/Review | 1        | 200         |
    # Add third RLI by clicking '+' button on the second row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 2 row
    # Provide input for the third RLI
    When I provide input for #OpportunityDrawer.RLITable view for 3 row
      | *name | date_closed | best_case | sales_stage       | quantity | likely_case |
      | RLI_3 | 12/01/2020  | 350       | Value Proposition | 1        | 300         |
    # Save new opportunity
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Navigate to Forecasts module
    When I choose Forecasts in modules menu

    # Select Time Period
    When I provide input for #Forecasts view
      | selectedTimePeriod |
      | 2020 Q4            |

    # Verify included RLIs are present in the worksheet
    Then I should see *RLI_1 in #Forecasts.ListView
    Then I should see *RLI_2 in #Forecasts.ListView
    Then I should not see *RLI_3 in #Forecasts.ListView

    # Verify Displayed Total in the footer of the worksheet
    Then I verify Displayed Total data on #Forecasts.Footer
      | fieldName | value   |
      | Best      | $400.00 |
      | Likely    | $300.00 |

    # Verify Overall Total in the footer of the worksheet
    Then I verify Overall Total data on #Forecasts.Footer
      | fieldName | value   |
      | likely    | $600.00 |
      | best      | $750.00 |

    # Verify 'In Forecast' dashlet appearance
    Then I verify that InForecastDashlet element from #Dashboard.DashboardView still looks like InForecast1
    # Verify 'Forecast Bar Chart' dashlet appearance
    Then I verify that ForecastBarChart element from #Dashboard.DashboardView still looks like ForecastBarChart1

    # Add 'Exclude' filter
    When I add "Exclude" in #Forecasts.FilterView view

    # Verify RLI_3 is now present in the worksheet
    Then I should see *RLI_3 in #Forecasts.ListView

    # Verify header data (RLI_1 + RLI_2)
    Then I verify header data on #Forecasts
      | fieldName   | value   |
      | quota       | $0.00   |
      | likely_case | $300.00 |
      | best_case   | $400.00 |

    # Verify Displayed Total
    Then I verify Displayed Total data on #Forecasts.Footer
      | fieldName | value   |
      | likely    | $600.00 |
      | best      | $750.00 |

    # Verify Overall Total
    Then I verify Overall Total data on #Forecasts.Footer
      | fieldName | value   |
      | likely    | $600.00 |
      | best      | $750.00 |

    # Change commit_stage of RLI_3 to "Include"
    When I set values for *RLI_3 in #Forecasts.ListView
      | fieldName    | value   |
      | commit_stage | Include |

    # Verify header data (RLI_1 + RLI_2 + RLI_3)
    Then I verify header data on #Forecasts
      | fieldName   | value   |
      | quota       | $0.00   |
      | likely_case | $600.00 |
      | best_case   | $750.00 |

    # Verify 'In Forecast' dashlet appearance
    Then I verify that InForecastDashlet element from #Dashboard.DashboardView still looks like InForecast2
    # Verify 'Forecast Bar Chart' dashlet appearance
    Then I verify that ForecastBarChart element from #Dashboard.DashboardView still looks like ForecastBarChart2

    # Remove 'Exclude' filter
    When I remove "Exclude" filter in #Forecasts.FilterView view

    # Hide RHS panel to add more real estate
    When I click ToggleSidePanel button on #Forecasts header

    # Change Likely and Best value of RLI_1
    When I set values for *RLI_1 in #Forecasts.ListView
      | fieldName   | value |
      | likely_case | 200   |
      | best_case   | 250   |

    # Show RHS pane
    When I click ToggleSidePanel button on #Forecasts header

    # Verify header data
    Then I verify header data on #Forecasts
      | fieldName   | value   |
      | quota       | $0.00   |
      | likely_case | $700.00 |
      | best_case   | $850.00 |

    # Change sales tage of RLI_2 to 'Closed Won'
    When I set values for *RLI_2 in #Forecasts.ListView
      | fieldName   | value      |
      | sales_stage | Closed Won |

    # Check header data
    Then I verify header data on #Forecasts
      | fieldName   | value   |
      | quota       | $0.00   |
      | likely_case | $700.00 |
      | best_case   | $800.00 |

    # Verify 'In Forecast' dashlet appearance
    Then I verify that InForecastDashlet element from #Dashboard.DashboardView still looks like InForecast3
    # Verify 'Forecast Bar Chart' dashlet appearance
    Then I verify that ForecastBarChart element from #Dashboard.DashboardView still looks like ForecastBarChart3

    # Click Commit button
    When I click Commit button on #Forecasts header
    When I close alert
