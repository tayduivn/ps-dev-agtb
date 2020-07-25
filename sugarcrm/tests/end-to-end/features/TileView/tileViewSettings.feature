# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@job1 @ent-only
Feature: Tile View Settings

  Background:
    Given I am logged in

  @tile_view_settings @opportunities_disable_tile_view @pr
  Scenario: Tile View Settings > Disable/Enable Tile View for Opportunities > Check Tile View button

    Given Accounts records exist:
      | *name |
      | Acc_1 |

    # Hide opportunities module
    When I hide "Opportunities" module in #TileViewSettings view

    # Check state of the Tile View button
    When I choose Opportunities in modules menu
    Then I verify button state in #OpportunitiesList.FilterView
      | control  | Disabled |
      | TileView | true     |

    # Enable Tile View for opportunities module
    When I enable "Opportunities" module in #TileViewSettings view with the following settings:
      | table_header | tile_options_header | tile_options_body                                      | records_per_column |
      | Sales Stage  | Opportunity Name    | Account Name, Expected Close Date, Likely, Lead Source | 15                 |

    # Check state of the Tile View button
    When I choose Opportunities in modules menu
    Then I verify button state in #OpportunitiesList.FilterView
      | control  | Disabled |
      | TileView | false    |

    # Navigate to Tile View
    When I select TileView in #OpportunitiesList.FilterView

    # Create New opportunity while in Tile View
    When I click tileViewCreate button on #OpportunitiesList header
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
    When I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify record's information displayed in the tile
    Then I verify *Opp_1 tile field values in #OpportunitiesTileView view
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
    And I select TileView in #CasesList.FilterView
    Then I should be redirected to "Cases/pipeline" route

    # Verify tile view is updated
    Then I verify tile view column headers in #CasesTileView view
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
    And I select TileView in #CasesList.FilterView

    # Verify that default columns are restored
    Then I verify tile view column headers in #CasesTileView view
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
    And I select TileView in #OpportunitiesList.FilterView

    # Switch to Opportunities by Sales Stage tab
    When I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify that columns are successfully re-arranged
    Then I verify tile view column headers in #OpportunitiesTileView view
      | value         | position |
      | Prospecting   | 2        |
      | Qualification | 5        |

    # Create New opportunity while in Tile View
    When I click tileViewCreate button on #OpportunitiesList header
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
      | RLI1  | 04/19/2020  | Prospecting | 2000        |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    When I provide input for #OpportunityDrawer.RLITable view for 2 row
      | *name | date_closed | sales_stage   | likely_case |
      | RLI2  | 04/19/2021  | Qualification | 2000        |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Verify column the opportunity tile appears under Qualification column
    Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesTileView view

    When I drag *Opp_1 tile to "Perception Analysis" column in #OpportunitiesTileView view

    Then I verify the [*Opp_1] records are under "Perception Analysis" column in #OpportunitiesTileView view

    # Restore default order
    When I drag-n-drop column header items on "Opportunities" module in #TileViewSettings view:
      | sourceItem    | destination | position |
      | Prospecting   | white_list  | 1        |
      | Qualification | white_list  | 2        |

    Then I verify tile view column headers in #OpportunitiesTileView view
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
    And I select TileView in #OpportunitiesList.FilterView
    Then I should be redirected to "Opportunities/pipeline" route

    # Switch to Opportunities by Sales Stage tab
    When I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify that 'Closed Won' and 'Closed Lost' columns are enabled
    Then I verify tile view column headers in #OpportunitiesTileView view
      | value       | position |
      | Closed Won  | 2        |
      | Closed Lost | 4        |

    # Create New opportunity while in Tile View
    When I click tileViewCreate button on #OpportunitiesList header
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
      | RLI1  | 04/19/2020  | Prospecting | 2000        |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    When I provide input for #OpportunityDrawer.RLITable view for 2 row
      | *name | date_closed | sales_stage   | likely_case |
      | RLI2  | 04/19/2020  | Qualification | 2000        |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Verify the opportunity tile appears under 'Qualification' column
    Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesTileView view
    # Drag opportunity tile to 'Closed Lost' column
    When I drag *Opp_1 tile to "Closed Lost" column in #OpportunitiesTileView view
    # Verify that the opportunity tile appears under 'Closed Lost' column
    Then I verify the [*Opp_1] records are under "Closed Lost" column in #OpportunitiesTileView view

    # Drag opportunity tile to 'Closed Won' column
    When I drag *Opp_1 tile to "Closed Won" column in #OpportunitiesTileView view
    # Verify that the opportunity tile cannot be moved to 'Closed Won' column and is under 'Closed Lost' column
    Then I verify the [*Opp_1] records are under "Closed Lost" column in #OpportunitiesTileView view

    # Disable 'Closed Won' and 'Closed Lost' columns in Opportunities Tile View
    When I drag-n-drop column header items on "Opportunities" module in #TileViewSettings view:
      | sourceItem  | destination | position |
      | Closed Won  | black_list  | 0        |
      | Closed Lost | black_list  | 1        |


  @tile_view_settings @SS-287 @AT-339 @pr
  Scenario: Tile View Settings > Cases > Change tile view header
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    # Update Tile View for cases module
    When I update "Cases" module in #TileViewSettings view with the following settings:
      | table_header | tile_options_header | tile_options_body                          | records_per_column |
      | Priority     | Subject             | Account Name~r, Status, Priority~r, Source | 15                 |

    # Navigate to Cases > Tile View
    When I choose Cases in modules menu
    And I select TileView in #CasesList.FilterView
    Then I should be redirected to "Cases/pipeline" route

    # Verify tile view is updated
    Then I verify tile view column headers in #CasesTileView view
      | value  | position |
      | High   | 1        |
      | Medium | 2        |
      | Low    | 3        |

    # Create a new case while in Tile View
    When I click tileViewCreate button on #CasesList header
    When I click show more button on #CasesDrawer view
    When I provide input for #CasesDrawer.HeaderView view
      | *   | name               |
      | C_1 | High Priority Case |
    When I provide input for #CasesDrawer.RecordView view
      | *   | account_name | priority | source   | status   |
      | C_1 | Acc_1        | High     | Internal | Assigned |
    When I click Save button on #CasesDrawer header
    When I close alert

    # Verify record's information displayed correctly in the tile
    Then I verify *C_1 tile field values in #CasesTileView view
      | value              |
      | High Priority Case |
      | Assigned           |
      | Internal           |

    # Verify case appears under correct column
    Then I verify the [*C_1] records are under "P1" column in #CasesTileView view

    # Drag tile to another column and verify
    When I drag *C_1 tile to "Medium" column in #CasesTileView view
    Then I verify the [*C_1] records are under "P2" column in #CasesTileView view

    # Drag tile to another column and verify
    When I drag *C_1 tile to "Low" column in #CasesTileView view
    Then I verify the [*C_1] records are under "P3" column in #CasesTileView view

    # Restore defaults
    # Update Tile View for cases module
    When I update "Cases" module in #TileViewSettings view with the following settings:
      | table_header | tile_options_header | tile_options_body                          | records_per_column |
      | Status       | Subject             | Account Name, Priority, Status~r, Source~r |                    |

    # Verify tile appears under correct column after tile header is changed
    Then I verify the [*C_1] records are under "Assigned" column in #CasesTileView view

    # Verify tile contains correct information
    Then I verify *C_1 tile field values in #CasesTileView view
      | value              |
      | High Priority Case |
      | Acc_1              |
      | Low                |

