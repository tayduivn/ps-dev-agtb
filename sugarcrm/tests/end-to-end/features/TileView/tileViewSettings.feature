# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@job1
Feature: Tile View Settings

  Background:
    Given I am logged in

  @tile_view_settings @opportunities_disable_tile_view
  Scenario: Tile View Settings > Disable/Enable Tile View for Opportunities > Check Tile View button

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

    # Create New opportunity while in Tile View
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


  @tile_view_settings @activate_columns @pr
  Scenario: Tile View Settings > Cases > Activate/Hide Tile View columns
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    # remove a few columns from tile view
    When I drag-n-drop column header items on "Cases" module in #TileViewSettings view:
      | sourceItem | destination | position |
      | New        | black_list  | 0        |
      | Closed     | black_list  | 1        |
      | Rejected   | black_list  | 2        |

    # Navigate to Cases > Tile View
    When I choose Cases in modules menu
    And I select VisualPipeline in #CasesList.FilterView
    Then I should be redirected to "Cases/pipeline" route

    # Verify tile view is updated
    Then I verify pipeline column headers in #CasesPipelineView view
      | value         | position |
      | Assigned      | 1        |
      | Pending Input | 2        |
      | Duplicate     | 3        |

    # Restore default column positions
    When I drag-n-drop column header items on "Cases" module in #TileViewSettings view:
      | sourceItem | destination | position |
      | New        | white_list  | 1        |
      | Closed     | white_list  | 3        |
      | Rejected   | white_list  | 5        |


    # Navigate to tile view
    When I choose Cases in modules menu
    And I select VisualPipeline in #CasesList.FilterView

    # Verify that default columns are restored
    Then I verify pipeline column headers in #CasesPipelineView view
      | value         | position |
      | New           | 1        |
      | Assigned      | 2        |
      | Closed        | 3        |
      | Pending Input | 4        |
      | Rejected      | 5        |
      | Duplicate     | 6        |


  @tile_view_settings @activate_columns @pr
  Scenario: Tile View Settings > Opportunities > Re-arrange columns
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    # Move column to other position in Opportunities Tile View
    When I drag-n-drop column header items on "Opportunities" module in #TileViewSettings view:
      | sourceItem    | destination | position |
      | Prospecting   | white_list  | 3        |
      | Qualification | white_list  | 5        |


    # Navigate to Opportunities > Tile View
    When I choose Opportunities in modules menu
    And I select VisualPipeline in #OpportunitiesList.FilterView

    # Switch to Opportunities by Sales Stage tab
    When I select pipelineByStage tab in #OpportunitiesPipelineView view

    # Verify that columns are successfully re-arranged
    Then I verify pipeline column headers in #OpportunitiesPipelineView view
      | value         | position |
      | Prospecting   | 2        |
      | Qualification | 5        |

    # Create New opportunity while in Tile View
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
      | *name | date_closed | sales_stage | likely_case |
      | RLI1  | 04/19/2020  | Closed Won  | 2000        |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    When I provide input for #OpportunityDrawer.RLITable view for 2 row
      | *name | date_closed | sales_stage | likely_case |
      | RLI2  | 04/19/2021  | Closed Lost | 2000        |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

     # Temporary commented ! uncomment after SS-231 is fixed

    # Verify column the opportunity tile appears under
#   Then I verify the [*Opp_1] records are under "Closed Won" column in #OpportunitiesPipelineView view

#    When I drag *Opp_1 tile to "Perception Analysis" column in #OpportunitiesPipelineView view
#
#    Then I verify the [*Opp_1] records are under "Perception Analysis" column in #OpportunitiesPipelineView view

    # Restore default order
    When I drag-n-drop column header items on "Opportunities" module in #TileViewSettings view:
      | sourceItem    | destination | position |
      | Prospecting   | white_list  | 1        |
      | Qualification | white_list  | 2        |

    Then I verify pipeline column headers in #OpportunitiesPipelineView view
      | value         | position |
      | Prospecting   | 1        |
      | Qualification | 2        |


  @tile_view_settings @rearrange_columns @pr
  Scenario: Tile View Settings > Opportunities > Re-arrange columns

    Given Accounts records exist:
      | *name |
      | Acc_1 |

    # Enable 'Closed Won' and 'Closed Lost' columns in Opportunities Tile View
    When I drag-n-drop column header items on "Opportunities" module in #TileViewSettings view:
      | sourceItem  | destination | position |
      | Closed Won  | white_list  | 2        |
      | Closed Lost | white_list  | 4        |

    # Navigate to Cases > Tile View
    When I choose Opportunities in modules menu
    And I select VisualPipeline in #OpportunitiesList.FilterView
    Then I should be redirected to "Opportunities/pipeline" route

    # Switch to Opportunities by Sales Stage tab
    When I select pipelineByStage tab in #OpportunitiesPipelineView view

    # Verify that 'Closed Won' and 'Closed Lost' columns are enabled
    Then I verify pipeline column headers in #OpportunitiesPipelineView view
      | value       | position |
      | Closed Won  | 2        |
      | Closed Lost | 4        |

    # Create New opportunity while in Tile View
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
      | *name | date_closed | sales_stage | likely_case |
      | RLI1  | 04/19/2020  | Closed Won  | 2000        |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    When I provide input for #OpportunityDrawer.RLITable view for 2 row
      | *name | date_closed | sales_stage | likely_case |
      | RLI2  | 04/19/2021  | Closed Lost | 2000        |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Verify column the opportunity tile appears under
    Then I verify the [*Opp_1] records are under "Closed Won" column in #OpportunitiesPipelineView view

    # # Temporary commented ! uncomment after SS-231 is fixed
#    When I drag *Opp_1 tile to "Closed Lost" column in #OpportunitiesPipelineView view
#
#    Then I verify the [*Opp_1] records are under "Closed Lost" column in #OpportunitiesPipelineView view

    # Disable 'Closed Won' and 'Closed Lost' columns in Opportunities Tile View
    When I drag-n-drop column header items on "Opportunities" module in #TileViewSettings view:
      | sourceItem  | destination | position |
      | Closed Won  | black_list  | 0        |
      | Closed Lost | black_list  | 1        |
