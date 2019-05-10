# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@job5
Feature: Tile View Settings

  Background:
    Given I am logged in

  @opportunities_disable_tile_view @pr
  Scenario: Tile View Settings > Disable/Enable tile view for Opportunities > Check Tile View button

    Given Accounts records exist:
      | *name |
      | Acc_1 |

    # Hide opportunities module
    When I hide "Opportunities" module in #TileViewSettings view

    # Check state of the Tile View button
    When I choose Opportunities in modules menu
    Then I verify button state in #OpportunitiesList.FilterView
      | control        | Disabled |
      | VisualPipeline | true     |

    # Enable Tile View for opportunities module
    When I show "Opportunities" module in #TileViewSettings view with the following settings:
      | table_header | tile_options_header | tile_options_body                                      | records_per_column |
      | Sales Stage  | Name                | Account Name, Expected Close Date, Likely, Lead Source | 15                 |

    # Check state of the Tile View button
    When I choose Opportunities in modules menu
    Then I verify button state in #OpportunitiesList.FilterView
      | control        | Disabled |
      | VisualPipeline | false    |

    # Navigate to Tile View
    When I select VisualPipeline in #OpportunitiesList.FilterView

    # Create New opportunity while in tile view
    When I click pipelineCreate button on #OpportunitiesList header
    When I click show more button on #OpportunitiesDrawer view
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name            |
      | Opp_1 | New Opportunity |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | account_name | lead_source |
      | Opp_1 | Acc_1        | Cold Call   |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | *name | date_closed | sales_stage   | likely_case |
      | RLI1  | 04/19/2020  | Qualification | 2000        |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Switch to Opportunities by Sales Stage tab
    When I select pipelineByStage tab in #OpportunitiesPipelineView view

    # Verify record's information displayed in the tile
    Then I verify *Opp_1 tile field values in #OpportunitiesPipelineView view
      | value           |
      | New Opportunity |
      | Acc_1           |
      | 04/19/2020      |
      | $2,000.00       |
      | Cold Call       |
