# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules_forecasts @job6
Feature: Forecasts module

  Background:
    Given I use default account
    Given I launch App

  @forecasts_workflow_automation @pr
  Scenario: Forecasts sales manager to sales rep workflow automation

    # Create required records
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    # Create new non-admin user
    Given I create custom user "user"

    # Configure Forecast module - Forecasts module must be configured before creating RLI records
    When I configure Forecasts module
      | name                           | value |
      | show_binary_ranges.include.min | 60    |
      | show_binary_ranges.include.max | 100   |
      | show_binary_ranges.exclude.min | 0     |
      | show_binary_ranges.exclude.max | 59    |

    # Log in as sales manager
    When I use default account
    Given I open about view and login
    When I choose Forecasts in modules menu
    When I wait for 5 seconds

    # Select Time Period
    When I provide input for #Forecasts view
      | selectedTimePeriod |
      | 2020 Q4            |

    # Verify quota, likely and best amounts in the Top Info Bar
    Then I verify forecasts data on #Forecasts.SalesManagerWorksheet.TopInfoBar
      | fieldName   | value |
      | quota       | $0.00 |
      | worst_case  | $0.00 |
      | likely_case | $0.00 |
      | best_case   | $0.00 |

    # Assign Quota to sales rep
    When I set values for *user in #Forecasts.SalesManagerWorksheet.ListView
      | fieldName | value |
      | quota     | 1000  |

    # Click Assign Quota button
    When I open actions menu in #ForecastsRecord
    And I choose AssignQuota from actions menu in #ForecastsRecord

    # Verify quota amount in the top info bar
    Then I verify forecasts data on #Forecasts.SalesManagerWorksheet.TopInfoBar
      | fieldName | value     |
      | quota     | $1,000.00 |

    # Verify and Close Alert
    Then I check alert
      | type     | message                                          |
      | Success: | Success: Quotas have been successfully assigned. |
    When I close alert

    # Verify 'In Forecast' and 'Forecasts Bar Chart' dashlet appearances
     Then I verify that FirstDashlet element from #Dashboard.DashboardView still looks like salesManager_InForecast1
     Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesManager_ForecastBarChart1

    # Verify Totals in forecast manager's worksheet footer
    Then I verify forecasts Total amounts in #Forecasts.SalesManagerWorksheet.Footer
      | fieldName   | value     |
      | Quota       | $1,000.00 |
      | Likely_case | $0.00     |
      | Best_case   | $0.00     |
      | Worst_case  | $0.00     |

    # Logout and login as Sales Rep
    When I logout
    When I use account "user"
    When I open Opportunities view and login

    # Create new opportunity
    When I click Create button on #OpportunitiesList header
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name                    |
      | Opp_1 | Forecast Module Testing |
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

    # Verify included RLIs are present in sales rep the worksheet
    Then I should see *RLI_1 in #Forecasts.SalesRepWorksheet.ListView
    Then I should see *RLI_2 in #Forecasts.SalesRepWorksheet.ListView
    Then I should not see *RLI_3 in #Forecasts.SalesRepWorksheet.ListView

    # Verify Displayed Total in the sales rep worksheet footer
    Then I verify forecasts Displayed Total data on #Forecasts.SalesRepWorksheet.Footer
      | fieldName | value   |
      | Best      | $400.00 |
      | Likely    | $300.00 |

    # Verify Overall Total in the sales rep worksheet footer
    Then I verify forecasts Overall Total data on #Forecasts.SalesRepWorksheet.Footer
      | fieldName | value   |
      | likely    | $600.00 |
      | best      | $750.00 |

    # Verify 'In Forecast' and 'Forecasts Bar Chart' dashlet appearance
    Then I verify that FirstDashlet element from #Dashboard.DashboardView still looks like salesRep_InForecast1

    When I select Sales Stage in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesRep_ForecastBarChart1_salesStage

    When I select Probability in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesRep_ForecastBarChart1_probability

    When I select In Forecast in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesRep_ForecastBarChart1_inForecast

    # Add 'Exclude' filter to sales rep worksheet
    When I add "Exclude" in #Forecasts.SalesRepWorksheet.Filter view

    # Verify RLI_3 is now present in the worksheet
    Then I should see *RLI_3 in #Forecasts.SalesRepWorksheet.ListView

    # Verify quota, worst, likely and best amounts at the Info Bar
    Then I verify forecasts data on #Forecasts.SalesRepWorksheet.TopInfoBar
      | fieldName   | value     |
      | quota       | $1,000.00 |
      | worst_case  | $300.00   |
      | likely_case | $300.00   |
      | best_case   | $400.00   |

    # Verify Displayed Total in Forecasts Sales Rep worksheet footer
    Then I verify forecasts Displayed Total data on #Forecasts.SalesRepWorksheet.Footer
      | fieldName | value   |
      | likely    | $600.00 |
      | best      | $750.00 |

    # Verify Overall Total in Forecasts Sales Rep worksheet footer
    Then I verify forecasts Overall Total data on #Forecasts.SalesRepWorksheet.Footer
      | fieldName | value   |
      | likely    | $600.00 |
      | best      | $750.00 |

    # Change commit_stage of RLI_3 to "Include" in sales rep worksheet
    When I set values for *RLI_3 in #Forecasts.SalesRepWorksheet.ListView
      | fieldName    | value   |
      | commit_stage | Include |

    # Verify quota, likely and best amounts at the Info Bar
    Then I verify forecasts data on #Forecasts.SalesRepWorksheet.TopInfoBar
      | fieldName   | value     |
      | quota       | $1,000.00 |
      | worst_case  | $600.00   |
      | likely_case | $600.00   |
      | best_case   | $750.00   |

    # Verify 'In Forecast' and 'Forecasts Bar Chart' dashlet appearance
    Then I verify that FirstDashlet element from #Dashboard.DashboardView still looks like salesRep_InForecast2

    When I select Sales Stage in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesRep_ForecastBarChart2_salesStage

    When I select Probability in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesRep_ForecastBarChart2_probability

    When I select In Forecast in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesRep_ForecastBarChart2_inForecast

    # Remove 'Exclude' filter from forecast sales rep worksheet
    When I remove "Exclude" filter in #Forecasts.SalesRepWorksheet.Filter view

    # Hide RHS panel to add more real estate
    When I click ToggleSidePanel button on #Forecasts.SalesRepWorksheet header

    # Change Likely and Best value of RLI_1
    When I set values for *RLI_1 in #Forecasts.SalesRepWorksheet.ListView
      | fieldName   | value |
      | likely_case | 200   |
      | best_case   | 250   |

    # Show RHS pane
    When I click ToggleSidePanel button on #Forecasts.SalesRepWorksheet header

    # Verify quota, likely and best amounts at the Info Bar
    Then I verify forecasts data on #Forecasts.SalesRepWorksheet.TopInfoBar
      | fieldName   | value     |
      | quota       | $1,000.00 |
      | worst_case  | $600.00   |
      | likely_case | $700.00   |
      | best_case   | $850.00   |

    # Change sales stage of RLI_2 to 'Closed Won'
    When I set values for *RLI_2 in #Forecasts.SalesRepWorksheet.ListView
      | fieldName   | value      |
      | sales_stage | Closed Won |

    # Verify quota, likely and best amounts at the Info Bar
    Then I verify forecasts data on #Forecasts.SalesRepWorksheet.TopInfoBar
      | fieldName   | value     |
      | quota       | $1,000.00 |
      | worst_case  | $600.00   |
      | likely_case | $700.00   |
      | best_case   | $800.00   |

    # Verify 'In Forecast' and 'Forecasts Bar Chart' dashlet appearance
    Then I verify that FirstDashlet element from #Dashboard.DashboardView still looks like salesRep_InForecast3

    When I select Sales Stage in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesRep_ForecastBarChart3_salesStage

    When I select Probability in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesRep_ForecastBarChart3_probability

    When I select In Forecast in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesRep_ForecastBarChart3_inForecast

    # Click Commit button
    When I click Commit button on #Forecasts.SalesRepWorksheet header

    # Verify and Close Alert
    Then I check alert
      | type     | message                                                    |
      | Success: | Success: You have committed your Forecast to Administrator |
    When I close alert

    # Log In as Sales Manager
    When I logout
    When I use default account
    And I open Forecasts view and login

    # Select Time Period
    When I provide input for #Forecasts view
      | selectedTimePeriod |
      | 2020 Q4            |

    # Verify 'In Forecast' and 'Forecasts Bar Chart' dashlet appearances
    Then I verify that FirstDashlet element from #Dashboard.DashboardView still looks like salesManager_InForecast2

    When I select Best in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesManager_ForecastBarChart2_best

    When I select Worst in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesManager_ForecastBarChart2_worst

    When I select Likely in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesManager_ForecastBarChart2_likely

    # Verify quota, likely and best amounts at manager's worksheet Top Info Bar
    Then I verify forecasts data on #Forecasts.SalesManagerWorksheet.TopInfoBar
      | fieldName   | value     |
      | quota       | $1,000.00 |
      | worst_case  | $600.00   |
      | likely_case | $700.00   |
      | best_case   | $800.00   |

    # Hide RHS panel to add more real estate
    When I click ToggleSidePanel button on #Forecasts.SalesManagerWorksheet header

    # Change adjusted numbers
    When I set values for *user in #Forecasts.SalesManagerWorksheet.ListView
      | fieldName            | value |
      | worst_case_adjusted  | 800   |
      | likely_case_adjusted | 1000  |
      | best_case_adjusted   | 1200  |

    # Show RHS panel to add more real estate
    When I click ToggleSidePanel button on #Forecasts.SalesManagerWorksheet header

    # Verify Totals in Manager's worksheet
    Then I verify forecasts Total amounts in #Forecasts.SalesManagerWorksheet.Footer
      | fieldName            | value     |
      | quota                | $1,000.00 |
      | worst_case           | $600.00   |
      | worst_case_adjusted  | $800.00   |
      | likely_case          | $700.00   |
      | likely_case_adjusted | $1,000.00 |
      | best_case            | $800.00   |
      | best_case_adjusted   | $1,200.00 |

    # Click Commit button
    When I click Commit button on #Forecasts.SalesManagerWorksheet header
    # Verify and Close Alert
    Then I check alert
      | type     | message                                   |
      | Success: | Success: You have committed your Forecast |
    When I close alert

    # Verify quota, likely and best amounts at manager's worksheet Top Info Bar
    Then I verify forecasts data on #Forecasts.SalesManagerWorksheet.TopInfoBar
      | fieldName   | value     |
      | quota       | $1,000.00 |
      | worst_case  | $800.00   |
      | likely_case | $1,000.00 |
      | best_case   | $1,200.00 |

    # Verify 'In Forecast' and 'Forecasts Bar Chart' dashlet appearances
    # TODO renable this when screenshot is updated
    #Then I verify that FirstDashlet element from #Dashboard.DashboardView still looks like salesManager_InForecast3

    When I select Best in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesManager_ForecastBarChart3_best

    When I select Worst in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesManager_ForecastBarChart3_worst

    When I select Likely in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesManager_ForecastBarChart3_likely

    # Navigate to user's Forecast Sales rep worksheet by clicking the link in manager's worksheet
    When I click name field on *user record in #Forecasts.SalesManagerWorksheet.ListView view

    # Verify included RLIs are present in sales rep the worksheet
    Then I should see *RLI_1 in #Forecasts.SalesRepWorksheet.ListView
    Then I should see *RLI_2 in #Forecasts.SalesRepWorksheet.ListView
    Then I should see *RLI_3 in #Forecasts.SalesRepWorksheet.ListView

    # Verify 'In Forecast' and 'Forecasts Bar Chart' dashlet appearances
    # TODO renable this when screenshot is updated
    #Then I verify that FirstDashlet element from #Dashboard.DashboardView still looks like salesRep_InForecast3_viewedByMngr

    When I select Sales Stage in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesRep_ForecastBarChart3_salesStage_viewedByMngr

    When I select Probability in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesRep_ForecastBarChart3_probability_viewedByMngr

    When I select In Forecast in #Dashboard.ForecastsBarChartDashlet
    Then I verify that SecondDashlet element from #Dashboard.DashboardView still looks like salesRep_ForecastBarChart3_inForecast_viewedByMngr
