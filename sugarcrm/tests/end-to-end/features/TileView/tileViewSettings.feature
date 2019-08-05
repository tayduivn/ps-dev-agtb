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

    When I drag-n-drop column header items on "Cases" module in #TileViewSettings view:
      | sourceItem | destination |
      | New        | black_list  |
      | Closed     | black_list  |
      | Rejected   | black_list  |

    # Navigate to Cases > Tile View
    When I choose Cases in modules menu
    And I select VisualPipeline in #CasesList.FilterView
    Then I should be redirected to "Cases/pipeline" route

    Then I verify pipeline column headers in #CasesPipelineView view
      | value         |
      | Assigned      |
      | Pending Input |
      | Duplicate     |

    When I drag-n-drop column header items on "Cases" module in #TileViewSettings view:
      | sourceItem | destination |
      | New        | white_list  |
      | Rejected   | white_list  |
      | Closed     | white_list  |

    When I choose Cases in modules menu
    And I select VisualPipeline in #CasesList.FilterView
    Then I should be redirected to "Cases/pipeline" route

    Then I verify pipeline column headers in #CasesPipelineView view
      | value         |
      | New           |
      | Assigned      |
      | Closed        |
      | Pending Input |
      | Rejected      |
      | Duplicate     |


  @tile_view_settings @activate_columns @pr
  Scenario: Tile View Settings > Opportunities > Activate/Hide Tile View columns

    Given Accounts records exist:
      | *name |
      | Acc_1 |

    # Enable 'Closed Won' and 'Closed Lost' columns in Opportunities Tile View
    When I drag-n-drop column header items on "Opportunities" module in #TileViewSettings view:
      | sourceItem  | destination |
      | Closed Won  | white_list  |
      | Closed Lost | white_list  |

    # Navigate to Cases > Tile View
    When I choose Opportunities in modules menu
    And I select VisualPipeline in #OpportunitiesList.FilterView
    Then I should be redirected to "Opportunities/pipeline" route

    # Switch to Opportunities by Sales Stage tab
    When I select pipelineByStage tab in #OpportunitiesPipelineView view

    # Verify that 'Closed Won' and 'Closed Lost' columns are enabled
    Then I verify pipeline column headers in #OpportunitiesPipelineView view
      | value       |
      | Closed Won  |
      | Closed Lost |

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

#    When I drag *Opp_1 tile to "Negotiation/Review" column in #OpportunitiesPipelineView view
#    When I wait for 3 seconds

    # Disable 'Closed Won' and 'Closed Lost' columns in Opportunities Tile View
    When I drag-n-drop column header items on "Opportunities" module in #TileViewSettings view:
      | sourceItem  | destination |
      | Closed Won  | black_list  |
      | Closed Lost | black_list  |