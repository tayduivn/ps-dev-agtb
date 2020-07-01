# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @job5
Feature: Sugar Sell Renewal Console Verification > Overview Tab
  As a sales agent I need to be able to verify main Renewal Console functionality

  Background:
    Given I am logged in


  @user_profile
  Scenario: User Profile > Change license type
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Sell" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value      |
      | Sugar Sell |
    When I click on Cancel button on #UserProfile


  @renewal_console @dashlets_verification
  Scenario: Renewal Console > Overview Tab > Dashlets screenshots verification > Pipeline

    # Configure Forecast module - Forecasts module must be configured before creating RLI records
    When I configure Forecasts module
      | name                           | value |
      | show_binary_ranges.include.min | 60    |
      | show_binary_ranges.include.max | 100   |
      | show_binary_ranges.exclude.min | 0     |
      | show_binary_ranges.exclude.max | 59    |

    Given Accounts records exist:
      | *name     |
      | Account 1 |
    # Generate opportunity record with RLIs with different sales stages linked to it
    And Opportunities records exist related via opportunities link:
      | *name |
      | Opp_1 |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage       | quantity | assigned_user_id |
      | RLI_1 | 2021-10-01T19:20:22+00:00 | 2000       | 3000        | 4000      | Prospecting       | 1        | 1                |
      | RLI_2 | 2021-10-01T19:20:22+00:00 | 2000       | 3000        | 4000      | Qualification     | 1        | 1                |
      | RLI_3 | 2021-10-01T19:20:22+00:00 | 2000       | 3000        | 4000      | Needs Analysis    | 1        | 1                |
      | RLI_4 | 2021-10-01T19:20:22+00:00 | 2000       | 3000        | 4000      | Value Proposition | 1        | 1                |

    # Navigate to Renewal Console
    When I choose Home in modules menu and select "Renewals Console" menu item

    # Select Overview tab in Renewal Console
    When I select Overview tab in #RenewalsConsoleView

    # Select '2021 Q3' in time periods dropdown
    When I select "2021 Q3" in #RenewalsConsoleView.PipelineDashlet

    # Verify that dashlet is empty (nothing to display)
    Then I verify 'No data available.' message appears in #RenewalsConsoleView.PipelineDashlet

    # Select '2021 Q4' in time periods dropdown
    When I select "2021 Q4" in #RenewalsConsoleView.PipelineDashlet

    # Verify Pipeline chart is updated
    # Then I verify that dashboard2by2_top_right element from #RenewalsConsoleView still looks like PipelineChart_1

    # Generate opportunity record with RLIs with different sales stages linked to it
    Given Opportunities records exist:
      | *name |
      | Opp_2 |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_2:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage         | quantity | assigned_user_id |
      | RLI_5 | 2021-10-01T19:20:22+00:00 | 2000       | 3000        | 4000      | Id. Decision Makers | 1        | 1                |
      | RLI_6 | 2021-10-01T19:20:22+00:00 | 2000       | 3000        | 4000      | Perception Analysis | 1        | 1                |

    # Refresh dashlet
    When I refresh #RenewalsConsoleView.PipelineDashlet dashlet

    # Verify Pipeline chart is updated
    # Then I verify that dashboard2by2_top_right element from #RenewalsConsoleView still looks like PipelineChart_2

    # Navigate to RLI module and delete one RLI item
    When I delete *RLI_1 record in RevenueLineItems list view

    # Go back to Renewal Console
    When I choose Home in modules menu

    # Select '2021 Q4' in time periods dropdown
    When I select "2021 Q4" in #RenewalsConsoleView.PipelineDashlet

    # Verify Pipeline chart is updated
    # Then I verify that dashboard2by2_top_right element from #RenewalsConsoleView still looks like PipelineChart_3


  @user_profile
  Scenario: User Profile > Change license type
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Enterprise" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value            |
      | Sugar Enterprise |
    When I click on Cancel button on #UserProfile
