# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@job2
Feature: Tile View feature

  Background:
    Given I am logged in

  @opportunities_tileView_bySalesStage @pr
  Scenario: Opportunities > Tile View > Opportunities by Sales Stage
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    And RevenueLineItems records exist:
      | *    | name              | date_closed               | likely_case | sales_stage |
      | RLI3 | RLI3 - ClosedWon  | 2020-12-12T19:20:22+00:00 | 300         | Closed Won  |
      | RLI4 | RLI4 - ClosedLost | 2020-10-19T19:20:22+00:00 | 300         | Closed Lost |

    And Opportunities records exist:
      | *name |
      | Opp_2 |

    # Navigate to Opportunities > Tile View
    When I choose Opportunities in modules menu
    When I select TileView in #OpportunitiesList.FilterView
    Then I should be redirected to "Opportunities/pipeline" route

    # Switch to Opportunities by Sales Stage tab
    When I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify column headers in Opportunities By Stage tab
    Then I verify tile view column headers in #OpportunitiesTileView view
      | value                | position |
      | Prospecting          | 1        |
      | Qualification        | 2        |
      | Needs Analysis       | 3        |
      | Value Proposition    | 4        |
      | Id. Decision Makers  | 5        |
      | Perception Analysis  | 6        |
      | Proposal/Price Quote | 7        |
      | Negotiation/Review   | 8        |

    # Create New opportunity while in tile view
    When I click tileViewCreate button on #OpportunitiesList header
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name            |
      | Opp_1 | New Opportunity |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | account_name |
      | Opp_1 | Acc_1        |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | *name | date_closed | sales_stage   | likely_case |
      | RLI1  | 04/19/2020  | Qualification | 2000        |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Filter specific record
    When I search for "New Opportunity" in #OpportunitiesList.FilterView view

    # Verify record's information displayed in the tile
    Then I verify *Opp_1 tile field values in #OpportunitiesTileView view
      | value           |
      | New Opportunity |
      | Acc_1           |
      | 04/19/2020      |
      | $2,000.00       |

    # Verify column the opportunity tile appears under
    Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesTileView view

    # Verify state of the tile delete button
    Then I verify *Opp_1 tile delete button state in #OpportunitiesTileView view
      | Disabled |
      | false    |

    # Add second RLI record to opportunity
    When I select *Opp_1 in #OpportunitiesTileView
    When I create_new record from revenuelineitems subpanel on #Opp_1Record view
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | *name |
      | RLI2  |
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | date_closed | sales_stage    | likely_case |
      | 05/20/2020  | Needs Analysis | 2000        |
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert

    # Navigate to Tile View
    When I choose Opportunities in modules menu
    When I select TileView in #OpportunitiesList.FilterView
    # Switch to Opportunities by Sales Stage tab
    When I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify record's information displayed in the tile
    Then I verify *Opp_1 tile field values in #OpportunitiesTileView view
      | value           |
      | New Opportunity |
      | Acc_1           |
      | 05/20/2020      |
      | $4,000.00       |

    # Verify column the opportunity tile appears under
    Then I verify the [*Opp_1] records are under "Needs Analysis" column in #OpportunitiesTileView view

    # Add Closed Won RLI and Closed Lost RLI to opportunity through mass update
    When I perform mass update of RevenueLineItems [*RLI3, *RLI4] with the following values:
      | fieldName        | value           |
      | opportunity_name | New Opportunity |

    # Navigate to Tile View
    When I choose Opportunities in modules menu
    When I select TileView in #OpportunitiesList.FilterView
    # Switch to Opportunities by Sales Stage tab
    When I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify record's information displayed in the tile
    Then I verify *Opp_1 tile field values in #OpportunitiesTileView view
      | value           |
      | New Opportunity |
      | Acc_1           |
      | 12/12/2020      |
      | $4,300.00       |

    # Verify state of the tile delete button
    Then I verify *Opp_1 tile delete button state in #OpportunitiesTileView view
      | Disabled |
      | true     |

    # Verify column the opportunity tile appears under
    Then I verify the [*Opp_1] records are under "Needs Analysis" column in #OpportunitiesTileView view

    # remove Closed Won RLI and Closed Lost RLI from opportunity through mass update
    When I perform mass update of RevenueLineItems [*RLI3, *RLI4] with the following values:
      | fieldName        | value |
      | opportunity_name | Opp_2 |

    # Navigate to Tile View
    When I choose Opportunities in modules menu
    When I select TileView in #OpportunitiesList.FilterView
    # Switch to Opportunities by Sales Stage tab
    When I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify column the opportunity tile appears under
    Then I verify the [*Opp_1] records are under "Needs Analysis" column in #OpportunitiesTileView view
    # Closed Won and closed lost columns have to be activated first
    # Then I verify the [*Opp_2] records are under "Closed Won" column in #OpportunitiesTileView view

    # Delete record in Tile View
    When I delete *Opp_1 in #OpportunitiesTileView view

    # Switch tab in Opportunities Tile View
    When I select opportunitiesByTime tab in #OpportunitiesTileView view

    # Navigate to the list view
    When I select ListView in #OpportunitiesList.FilterView


  @cases_tileView
  Scenario: Cases > Tile View
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    # Navigate to Cases > Tile View
    When I choose Cases in modules menu
    When I select TileView in #CasesList.FilterView
    Then I should be redirected to "Cases/pipeline" route

    # Verify column headers in Opportunities By Stage tab
    Then I verify tile view column headers in #CasesTileView view
      | value         | position |
      | New           | 1        |
      | Assigned      | 2        |
      | Closed        | 3        |
      | Pending Input | 4        |
      | Rejected      | 5        |
      | Duplicate     | 6        |

    # Create New Case while in Tile View
    When I click tileViewCreate button on #CasesList header
    When I provide input for #CasesDrawer.HeaderView view
      | *  | name   |
      | C2 | Case 2 |
    When I provide input for #CasesDrawer.RecordView view
      | *  | account_name | priority | status   |
      | C2 | Acc_1        | Medium   | Assigned |
    When I click Save button on #CasesDrawer header
    When I close alert

    # Verify column the case tile appears under
    Then I verify the [*C2] records are under "Assigned" column in #CasesTileView view

    # Verify record's information displayed in the tile
    Then I verify *C2 tile field values in #CasesTileView view
      | value  |
      | Case 2 |
      | Acc_1  |
      | Medium |

    # Change Case Status
    When I select *C2 in #CasesTileView
    When I click Edit button on #C2Record header
    When I provide input for #C2Record.RecordView view
      | *  | priority | status |
      | C2 | High     | Closed |
    When I click Save button on #C2Record header
    When I close alert

    When I choose Cases in modules menu
    When I select TileView in #CasesList.FilterView

    # Verify column the case tile appears under
    Then I verify the [*C2] records are under "Closed" column in #CasesTileView view

    # Verify record's information displayed in the tile
    Then I verify *C2 tile field values in #CasesTileView view
      | value  |
      | Case 2 |
      | Acc_1  |
      | High   |


  @tasks_tileView
  Scenario: Tasks > Tile View

    Given Accounts records exist:
      | *name |
      | Acc_1 |
    And Opportunities records exist:
      | *name |
      | Opp_1 |
    And Contacts records exist:
      | *     | first_name | last_name | email                              |
      | Con_1 | cFirst     | cLast     | cFirst.cLast@example.org (primary) |

    # Navigate to Tasks > Tile View
    When I choose Tasks in modules menu
    When I select TileView in #TasksList.FilterView
    Then I should be redirected to "Tasks/pipeline" route

    # Verify column headers in Opportunities By Stage tab
    Then I verify tile view column headers in #TasksTileView view
      | value         | position |
      | Not Started   | 1        |
      | In Progress   | 2        |
      | Completed     | 3        |
      | Pending Input | 4        |
      | Deferred      | 5        |

    # Create New Task while in Tile View
    When I click tileViewCreate button on #TasksList header
    When I click show more button on #TasksDrawer view
    When I provide input for #TasksDrawer.HeaderView view
      | *  | name   |
      | T1 | Task 1 |
    When I provide input for #TasksDrawer.RecordView view
      | *  | date_due           | status      | priority | parent_name   | contact_name |
      | T1 | 05/10/2020-02:00pm | Not Started | High     | Account,Acc_1 | cFirst cLast |

    When I click Save button on #TasksDrawer header
    When I close alert

    # Verify column the task tile appears under
    Then I verify the [*T1] records are under "Not Started" column in #TasksTileView view

    # Verify record's information displayed in the tile
    Then I verify *T1 tile field values in #TasksTileView view
      | value              |
      | Task 1             |
      | cFirst cLast       |
      | Acc_1              |
      | 05/10/2020 02:00pm |

    # Change Task Status
    When I select *T1 in #TasksTileView
    When I click Edit button on #T1Record header
    When I provide input for #T1Record.RecordView view
      | *  | priority | status      | parent_name       | date_due           |
      | C2 | Medium   | In Progress | Opportunity,Opp_1 | 06/10/2020-12:00pm |
    When I click Save button on #T1Record header
    When I close alert

    When I choose Tasks in modules menu
    When I select TileView in #TasksList.FilterView

    # Verify column the case tile appears under
    Then I verify the [*T1] records are under "In Progress" column in #TasksTileView view

    # Verify record's information displayed in the tile
    Then I verify *T1 tile field values in #TasksTileView view
      | value              |
      | Task 1             |
      | cFirst cLast       |
      | Opp_1              |
      | 06/10/2020 12:00pm |


  @leads_tileView
  Scenario: Leads > Tile View
    Given 3 Leads records exist:
      | *           | first_name    | last_name     | account_name          | title             | email                                     | status |
      | L_{{index}} | Lead{{index}} | Lead{{index}} | Lead{{index}} Account | Software Engineer | lead{{index}}.sugar@example.org (primary) | New    |

    # Navigate to List > Tile View
    When I choose Leads in modules menu
    When I select TileView in #LeadsList.FilterView
    Then I should be redirected to "Leads/pipeline" route

    # Verify column headers in Opportunities By Stage tab
    Then I verify tile view column headers in #LeadsTileView view
      | value      | position |
      | New        | 1        |
      | Assigned   | 2        |
      | In Process | 3        |
      | Converted  | 4        |
      | Recycled   | 5        |
      | Dead       | 6        |

    # Create New Lead while in Tile View
    When I click tileViewCreate button on #LeadsList header
    When I click show more button on #LeadsDrawer view
    When I provide input for #LeadsDrawer.HeaderView view
      | *   | first_name | last_name |
      | L_4 | Lead4      | Lead4     |
    When I provide input for #LeadsDrawer.RecordView view
      | *   | title                | phone_work     | website      | account_name     | status | email                   |
      | L_4 | Software QA Engineer | (639) 881-8398 | sugarcrm.com | Tester's Account | New    | lead4.sugar@example.org |

    When I click Save button on #LeadsDrawer header
    When I close alert

    # Verify column the task tile appears under
    Then I verify the [*L_1, *L_2, *L_3, *L_4] records are under "New" column in #LeadsTileView view

    # Verify record's information displayed in the tile
    Then I verify *L_4 tile field values in #LeadsTileView view
      | value                   |
      | Lead4 Lead4             |
      | lead4.sugar@example.org |
      | Tester's Account        |
      | (639) 881-8398          |

    # Change Lead Status
    When I select *L_1 in #LeadsTileView
    When I click Edit button on #L_1Record header
    When I provide input for #L_1Record.RecordView view
      | *   | title             | phone_work     | website      | account_name | status   | email                    |
      | L_1 | Software Engineer | (936) 188-8398 | sugarcrm.com | QA's Account | Assigned | lead4.sweets@example.org |
    When I click Save button on #L_1Record header
    When I close alert

    # Navigate to Leads > Tile View
    When I choose Leads in modules menu
    When I select TileView in #LeadsList.FilterView

    # Verify column the case tile appears under
    Then I verify the [*L_1] records are under "Assigned" column in #LeadsTileView view

    # Verify record's information displayed in the tile
    Then I verify *L_1 tile field values in #LeadsTileView view
      | value                    |
      | Lead1 Lead1              |
      | lead4.sweets@example.org |
      | QA's Account             |
      | (936) 188-8398           |

    # Move tiles
    When I drag *L_2 tile to "In Process" column in #LeadsTileView view
    When I drag *L_3 tile to "Recycled" column in #LeadsTileView view
    When I drag *L_4 tile to "Dead" column in #LeadsTileView view

    # Verify that tiles are moved successfully
    Then I verify the [*L_2, *L_3, *L_4] records are not under "New" column in #LeadsTileView view
    Then I verify the [*L_2] records are under "In Process" column in #LeadsTileView view
    Then I verify the [*L_3] records are under "Recycled" column in #LeadsTileView view
    Then I verify the [*L_4] records are under "Dead" column in #LeadsTileView view


  @leads_tileView_convert_lead @pr
  Scenario: Leads > Tile View > Convert Lead in Tile View
    Given 1 Leads records exist:
      | *           | first_name    | last_name     | account_name          | title             | email                                     | status |
      | L_{{index}} | Lead{{index}} | Lead{{index}} | Lead{{index}} Account | Software Engineer | lead{{index}}.sugar@example.org (primary) | New    |

    # Navigate to List > Tile View
    When I choose Leads in modules menu
    When I select TileView in #LeadsList.FilterView
    Then I should be redirected to "Leads/pipeline" route

      # Move tile to Converted column > Cancel
    When I drag *L_1 tile to "Converted" column in #LeadsTileView view

    # Generate ID for Contact record
    When I provide input for #L_1LeadConversionDrawer.ContactContent view
      | *  |
      | C1 |

    # Create Account Record
    When I provide input for #L_1LeadConversionDrawer.AccountContent view
      | *  |
      | A1 |

    # Create Opportunity record
    When I provide input for #L_1LeadConversionDrawer.OpportunityContent view
      | *  | name            |
      | O1 | New Opportunity |
    When I click CreateRecord button on #LeadConversionDrawer.OpportunityContent

    # Cancel lead conversion process
    When I click Cancel button on #LeadConversionDrawer header

    # Verify that conversion process is successfully completed
    Then I verify the [*L_1] records are under "New" column in #LeadsTileView view

    # Move tile to Converted column > Save
    When I drag *L_1 tile to "Converted" column in #LeadsTileView view

    # Generate ID for Contact record
    When I provide input for #L_1LeadConversionDrawer.ContactContent view
      | *  |
      | C1 |

    # Create Account Record
    When I provide input for #L_1LeadConversionDrawer.AccountContent view
      | *  |
      | A1 |

    # Create Opportunity record
    When I provide input for #L_1LeadConversionDrawer.OpportunityContent view
      | *  | name            |
      | O1 | New Opportunity |
    When I click CreateRecord button on #LeadConversionDrawer.OpportunityContent

    # Finish lead conversion process
    When I click Save button on #LeadConversionDrawer header
    When I close alert

    # Verify that conversion process is successfully completed
    Then I verify the [*L_1] records are under "Converted" column in #LeadsTileView view

    # Move tile from Converted column
    When I drag *L_1 tile to "New" column in #LeadsTileView view

    # Verify that tile is not moved from the Converted column
    Then I verify the [*L_1] records are under "Converted" column in #LeadsTileView view

    When I select *L_1 in #LeadsTileView
    # Verify that label in the lead's header says 'Converted'
    Then I verify fields on #L_1Record.HeaderView
      | fieldName | value     |
      | converted | Converted |


  @tileView_filter
  Scenario: Opportunities > Tile View > Filter is sticky when moving between tile view tabs and list view
    Given Accounts records exist:
      | *name     |
      | Account_1 |

    Given Opportunities records exist:
      | *name | lead_source |
      | Opp_1 | Cold Call   |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed               | likely_case | sales_stage   |
      | RLI_1 | 2020-12-12T19:20:22+00:00 | 1000        | Qualification |

    Given Opportunities records exist:
      | *name | lead_source       |
      | Opp_2 | Existing Customer |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_2:
      | *name | date_closed               | likely_case | sales_stage |
      | RLI_2 | 2020-12-12T19:20:22+00:00 | 2000        | Prospecting |

    Given Opportunities records exist:
      | *name | lead_source |
      | Opp_3 | Direct Mail |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_3:
      | *name | date_closed               | likely_case | sales_stage    |
      | RLI_3 | 2020-12-12T19:20:22+00:00 | 3000        | Needs Analysis |

    # Link opportunities to the account to re-enforce the calculations
    When I perform mass update of all Opportunities with the following values:
      | fieldName    | value     |
      | account_name | Account_1 |

    # Create custom filter
    When I add custom filter 'New Filter 1' on the Opportunities list view with the following values:
      | fieldName   | filter_operator | filter_value           |
      | lead_source | is any of       | Cold Call, Direct Mail |

    # Navigate to Opportunities > Tile View
    When I select TileView in #OpportunitiesList.FilterView
    Then I should be redirected to "Opportunities/pipeline" route

    # Switch to Opportunities by Sales Stage tab
    When I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify that custom filter is applied in Opportunities -> Tile View
    Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are not under "Prospecting" column in #OpportunitiesTileView view
    Then I verify the [*Opp_3] records are under "Needs Analysis" column in #OpportunitiesTileView view

    # Navigate to Opportunities > List View
    When I select ListView in #OpportunitiesList.FilterView

    # Verify that custom filter is applied in Opportunities -> List View
    Then I should see [*Opp_1, *Opp_3] on Opportunities list view
    And I should not see [*Opp_2] on Opportunities list view

    # Remove custom filter
    When I delete custom filter 'New Filter 1' on the Opportunities list view

    # Navigate to Opportunities > Tile View
    When I select TileView in #OpportunitiesList.FilterView

    # Verify that custom filter is removed in Opportunities -> Tile View
    Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are under "Prospecting" column in #OpportunitiesTileView view
    Then I verify the [*Opp_3] records are under "Needs Analysis" column in #OpportunitiesTileView view

    # Navigate to Opportunities > List View
    When I select ListView in #OpportunitiesList.FilterView

    # Verify that custom filter is removed in Opportunities -> List View
    Then I should see [*Opp_1, *Opp_2, *Opp_3] on Opportunities list view


  @tileView_search
  Scenario: Opportunities > Tile View > Search string is 'sticky' when switch tabs in Opportunities Tile View
    Given Accounts records exist:
      | *name     |
      | Account_1 |

    Given Opportunities records exist:
      | *name | lead_source |
      | Opp_1 | Cold Call   |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | likely_case | sales_stage   |
      | RLI_1 | now + 30d   | 1000        | Qualification |

    Given Opportunities records exist:
      | *name | lead_source       |
      | Opp_2 | Existing Customer |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_2:
      | *name | date_closed | likely_case | sales_stage |
      | RLI_2 | now         | 2000        | Prospecting |

    Given Opportunities records exist:
      | *name | lead_source |
      | Opp_3 | Direct Mail |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_3:
      | *name | date_closed | likely_case | sales_stage    |
      | RLI_3 | now + 90d   | 3000        | Needs Analysis |

    Given Opportunities records exist:
      | *name   | lead_source |
      | Opp_3_1 | Direct Mail |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_3_1:
      | *name   | date_closed | likely_case | sales_stage    |
      | RLI_3_1 | now + 90d   | 3000        | Needs Analysis |

    # Link opportunities to the account to re-enforce the calculations
    When I perform mass update of all Opportunities with the following values:
      | fieldName    | value     |
      | account_name | Account_1 |

    # Navigate to Opportunities > Tile View
    When I select TileView in #OpportunitiesList.FilterView

    # Switch to Opportunities by Time tab
    When I select opportunitiesByTime tab in #OpportunitiesTileView view

    # Verify that records appear in the tile view
    Then I verify the [*Opp_1] records are under "now + 30d" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are under "now" column in #OpportunitiesTileView view
    Then I verify the [*Opp_3, *Opp_3_1] records are under "now + 90d" column in #OpportunitiesTileView view

    # Search for 'Opp_2' string in tile view
    When I search for "Opp_2" in #OpportunitiesList.FilterView view

    # Verify that search is applied
    Then I verify the [*Opp_1] records are not under "now + 30d" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are under "now" column in #OpportunitiesTileView view
    Then I verify the [*Opp_3, *Opp_3_1] records are not under "now + 90d" column in #OpportunitiesTileView view

    # Switch to Opportunities by Sales Stage tab
    When I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify that search is applied in Opportunities by Sales Stage tab
    Then I verify the [*Opp_1] records are not under "Qualification" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are under "Prospecting" column in #OpportunitiesTileView view
    Then I verify the [*Opp_3] records are not under "Needs Analysis" column in #OpportunitiesTileView view

    # Search for 'Opp_3' string in tile view
    When I search for "Opp_3" in #OpportunitiesList.FilterView view

    # Verify that search is applied in Opportunities by Sales Stage tab
    Then I verify the [*Opp_1] records are not under "Qualification" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are not under "Prospecting" column in #OpportunitiesTileView view
    Then I verify the [*Opp_3, *Opp_3_1] records are under "Needs Analysis" column in #OpportunitiesTileView view

    # Switch to Opportunities by Time tab
    When I select opportunitiesByTime tab in #OpportunitiesTileView view

    # Verify that search is applied in Opportunities by Time tab
    Then I verify the [*Opp_1] records are not under "now + 30d" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are not under "now" column in #OpportunitiesTileView view
    Then I verify the [*Opp_3, *Opp_3_1] records are under "now + 90d" column in #OpportunitiesTileView view

    # Switch back to list view
    When I select ListView in #OpportunitiesList.FilterView

    # Verify that there is no search applied in the opportunities list view
    Then I should see [*Opp_1, *Opp_2, *Opp_3, *Opp_3_1] on Opportunities list view


  @tasks_tileView_move @pr
  Scenario: Tasks > Tile View > Move tiles
    Given Accounts records exist:
      | *name |
      | Acc_1 |
    And 3 Tasks records exist related via tasks link to *Acc_1:
      | *          | name           | status      | priority | date_start          | date_due            | description               |
      | T{{index}} | Task {{index}} | Not Started | High     | 2020-04-16T14:30:00 | 2020-04-18T14:30:00 | Seedbed testing for Tasks |

    # Navigate to Tasks > Tile View
    When I choose Tasks in modules menu
    When I select TileView in #TasksList.FilterView

    # Verify column the task tile appears under 'Not Started' column
    Then I verify the [*T1, *T2, *T3] records are under "Not Started" column in #TasksTileView view

    # Move tiles
    When I drag *T1 tile to "In Progress" column in #TasksTileView view
    When I drag *T2 tile to "Completed" column in #TasksTileView view
    When I drag *T3 tile to "Pending Input" column in #TasksTileView view

    # Verify that tiles are moved successfully
    Then I verify the [*T1, *T2, *T3] records are not under "Not Started" column in #TasksTileView view
    Then I verify the [*T1] records are under "In Progress" column in #TasksTileView view
    Then I verify the [*T2] records are under "Completed" column in #TasksTileView view
    Then I verify the [*T3] records are under "Pending Input" column in #TasksTileView view

    # Move tiles
    When I drag *T1 tile to "Pending Input" column in #TasksTileView view
    When I drag *T2 tile to "Pending Input" column in #TasksTileView view

    # Verify that tiles are moved successfully
    Then I verify the [*T3, *T2, *T1] records are under "Pending Input" column in #TasksTileView view


  @cases_tileView_move_plus_validation @SS-194 @SS-195 @SS-196 @AT-340
  Scenario: Cases > Tile View > Trigger record validation by moving tile to another column
    Given Accounts records exist:
      | *name |
      | Acc_1 |
    And Cases records exist:
      | *   | name        | status |
      | C_1 | My New Case | New    |

    # Navigate to Cases > Tile View
    When I choose Cases in modules menu
    When I select TileView in #CasesList.FilterView

    # Verify that the tile appears under correct column
    Then I verify the [*C_1] records are under "New" column in #CasesTileView view

    # Move the tile to another column to trigger record validation
    When I drag *C_1 tile to "Assigned" column in #CasesTileView view
    When I provide input for #C_1Drawer.RecordView view
      | account_name | priority | type           | source   | description |
      | Acc_1        | High     | Administration | Internal | My Case     |
    # Cancel Changes
    When I click Cancel button on #C_1Drawer header

    # Verify the tile is returned to the original column
    Then I verify the [*C_1] records are under "New" column in #CasesTileView view

    # Move case tile to another column to trigger record validation
    When I drag *C_1 tile to "Assigned" column in #CasesTileView view
    When I provide input for #C_1Drawer.RecordView view
      | account_name | priority | type           | source   | description |
      | Acc_1        | High     | Administration | Internal | My Case     |
    # Save changes
    When I click Save button on #C_1Drawer header
    When I close alert

    # Verify the case tile appears under correct column
    Then I verify the [*C_1] records are under "Assigned" column in #CasesTileView view

    # Verify tile contains correct information
    Then I verify *C_1 tile field values in #CasesTileView view
      | value       |
      | My New Case |
      | Acc_1       |
      | High        |

    # Move case tile to another column
    When I drag *C_1 tile to "Rejected" column in #CasesTileView view

    # Verify the case tile appears under correct column & no drawer opens
    Then I verify the [*C_1] records are under "Rejected" column in #CasesTileView view


  @tileView_not_saved_filter @SS-324 @AT-344
  Scenario: Opportunities/Leads > Tile View > Filter is created but not saved
    Given Accounts records exist:
      | *name     |
      | Account_1 |

    # Create opportunity records with linked RLIs
    Given Opportunities records exist:
      | *name | lead_source | opportunity_type  |
      | Opp_1 | Cold Call   | Existing Business |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed               | likely_case | sales_stage   |
      | RLI_1 | 2020-12-12T19:20:22+00:00 | 1000        | Qualification |

    Given Opportunities records exist:
      | *name | lead_source       | opportunity_type  |
      | Opp_2 | Existing Customer | Existing Business |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_2:
      | *name | date_closed               | likely_case | sales_stage |
      | RLI_2 | 2020-12-12T19:20:22+00:00 | 2000        | Prospecting |

    Given Opportunities records exist:
      | *name | lead_source | opportunity_type |
      | Opp_3 | Direct Mail | New Business     |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_3:
      | *name | date_closed               | likely_case | sales_stage    |
      | RLI_3 | 2020-12-12T19:20:22+00:00 | 3000        | Needs Analysis |

    # Create lead records
    Given Leads records exist:
      | *      | first_name | last_name | account_name     | title             | email              | lead_source | status     |
      | Lead_1 | John       | Barlow    | John's Account   | Software Engineer | lead_1@example.net | Partner     | New        |
      | Lead_2 | Travis     | Hubbard   | Travis's Account | Software Engineer | lead_2@example.net | Employee    | Assigned   |
      | Lead_3 | Alex       | Nisevich  | Alex's Account   | Quality Engineer  | lead_3@example.net | Partner     | In Process |

    # Link opportunities to the account to re-enforce the calculations
    When I perform mass update of all Opportunities with the following values:
      | fieldName    | value     |
      | account_name | Account_1 |

    # Navigate to Opportunities > Tile View
    When I select TileView in #OpportunitiesList.FilterView
    # Switch to Opportunities by Stage tab
    And I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify that tiles are displayed in the tile view
    Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are under "Prospecting" column in #OpportunitiesTileView view
    Then I verify the [*Opp_3] records are under "Needs Analysis" column in #OpportunitiesTileView view

    # Build custom filter in Opportunities List View but Do NOT save it
    When I add but do not save custom filter 'New Filter 1' on the Opportunities list view with the following values:
      | fieldName        | filter_operator | filter_value           |
      | lead_source      | is any of       | Cold Call, Direct Mail |
      | opportunity_type | is any of       | Existing Business      |

    # Navigate to Opportunities > Tile View
    When I select TileView in #OpportunitiesList.FilterView
    # Switch to Opportunities by Stage tab
    And I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify that tiles are filtered properly
    Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are not under "Prospecting" column in #OpportunitiesTileView view
    Then I verify the [*Opp_3] records are not under "Needs Analysis" column in #OpportunitiesTileView view

    # Build custom filter in Leads Tile View but do NOT save it
    When I add but do not save custom filter 'New Filter 2' on the Leads tile view with the following values:
      | fieldName   | filter_operator | filter_value |
      | lead_source | is any of       | Partner      |
      | first_name  | starts with     | Alex         |

    # Verify that tiles are filtered properly in Leads Tile View
    Then I verify the [*Lead_3] records are under "In Process" column in #LeadsTileView view
    Then I verify the [*Lead_1] records are not under "New" column in #LeadsTileView view
    Then I verify the [*Lead_2] records are not under "Assigned" column in #LeadsTileView view

    # Return to Opportunities List view
    When I choose Opportunities in modules menu

    # Verify Opportunities list view content
    Then I should see [*Opp_1] on Opportunities list view
    And I should not see [*Opp_2, *Opp_3] on Opportunities list view

    # Switch to Opportunities > Tile View > Opportunities by Stage tab
    When I select TileView in #OpportunitiesList.FilterView
    And I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify that tiles are filtered properly
    Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are not under "Prospecting" column in #OpportunitiesTileView view
    Then I verify the [*Opp_3] records are not under "Needs Analysis" column in #OpportunitiesTileView view

    # Verify Leads list view content
    Then I should see [*Lead_3] on Leads list view
    And I should not see [*Lead_1, *Lead_2] on Leads list view

    # Switch to Tile View > Leads
    When I select TileView in #LeadsList.FilterView

    # Verify that tiles are filtered properly in Leads Tile View
    Then I verify the [*Lead_3] records are under "In Process" column in #LeadsTileView view
    Then I verify the [*Lead_1] records are not under "New" column in #LeadsTileView view
    Then I verify the [*Lead_2] records are not under "Assigned" column in #LeadsTileView view

    When I cancel custom filter on the Leads tile view

    # Verify that tiles are no longer filtered in Leads Tile View
    Then I verify the [*Lead_3] records are under "In Process" column in #LeadsTileView view
    Then I verify the [*Lead_1] records are under "New" column in #LeadsTileView view
    Then I verify the [*Lead_2] records are under "Assigned" column in #LeadsTileView view

    When I choose Opportunities in modules menu
    # Navigate to Opportunities > Tile View
    When I select TileView in #OpportunitiesList.FilterView
    # Switch to Opportunities by Stage tab
    And I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Save previously unsaved filter
    When I save custom filter on the Opportunities tile view

    # Verify that tiles are filtered properly
    Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are not under "Prospecting" column in #OpportunitiesTileView view
    Then I verify the [*Opp_3] records are not under "Needs Analysis" column in #OpportunitiesTileView view

    # Delete custom filter
    When I delete custom filter 'New Filter 1' on the Opportunities tile view

    # Verify that tiles are no longer filtered
    Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are under "Prospecting" column in #OpportunitiesTileView view
    Then I verify the [*Opp_3] records are under "Needs Analysis" column in #OpportunitiesTileView view


  @tileView_closedRLIs @SS-312 @AT-346 @pr
  Scenario: Opportunities/Leads > Tile View > Closed RLIs should not be updated
    Given Accounts records exist:
      | *name     |
      | Account_1 |
    # Create opportunity records with linked RLIs
    And Opportunities records exist related via opportunities link to *Account_1:
      | *name | lead_source | opportunity_type  |
      | Opp_1 | Cold Call   | Existing Business |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | likely_case | sales_stage |
      | RLI_1 | now         | 1000        | Closed Won  |
      | RLI_2 | now         | 2000        | Closed Lost |
      | RLI_3 | now         | 3000        | Prospecting |

    # Navigate to Opportunities module
    When I choose Opportunities in modules menu
    # Navigate to Opportunities > Tile View
    When I select TileView in #OpportunitiesList.FilterView
    # Switch to Opportunities by Stage tab
    And I select opportunitiesByTime tab in #OpportunitiesTileView view

    # Verify tile is located in the correct column
    Then I verify the [*Opp_1] records are under "now" column in #OpportunitiesTileView view
    # Move tile from current month to the next month
    When I drag *Opp_1 tile to "now + 30d" column in #OpportunitiesTileView view
    # Verify that tile is moved successfully
    Then I verify the [*Opp_1] records are under "now + 30d" column in #OpportunitiesTileView view

    # Open RLI subpanel in the opportunity record view and verify Expected Close Date of each RLI
    When I select *Opp_1 in #OpportunitiesTileView
    When I open the revenuelineitems subpanel on #Opp_1Record view
    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value |
      | name        | RLI_1 |
      | date_closed | now   |
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value |
      | name        | RLI_2 |
      | date_closed | now   |
    Then I verify fields for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName | value |
      | name      | RLI_3 |

    # Navigate to Opportunities module
    When I choose Opportunities in modules menu
    # Navigate to Opportunities > Tile View
    When I select TileView in #OpportunitiesList.FilterView
    # Switch to Opportunities by Stage tab
    And I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify tile is located in the correct column
    Then I verify the [*Opp_1] records are under "Prospecting" column in #OpportunitiesTileView view
    # Move tile from one sales stage to another
    When I drag *Opp_1 tile to "Qualification" column in #OpportunitiesTileView view
    # Verify that tile is moved successfully
    Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesTileView view

    # Open RLI subpanel in the opportunity record view and verify Sales Stage of each RLI
    When I select *Opp_1 in #OpportunitiesTileView
    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | name        | RLI_1      |
      | sales_stage | Closed Won |
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value       |
      | name        | RLI_2       |
      | sales_stage | Closed Lost |
    Then I verify fields for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value         |
      | name        | RLI_3         |
      | sales_stage | Qualification |


  @tileView_closedRLIs @SS-341 @AT-347
  Scenario: Opportunities/Leads > Tile View > Closed RLIs should not be updated
    Given Accounts records exist:
      | *name     |
      | Account_1 |
    # Create 'Closed Won' opportunity records with linked RLIs
    And Opportunities records exist related via opportunities link to *Account_1:
      | *name | lead_source | opportunity_type  |
      | Opp_1 | Cold Call   | Existing Business |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | likely_case | sales_stage |
      | RLI_1 | now         | 1000        | Closed Won  |
      | RLI_2 | now         | 2000        | Closed Lost |
    # Create 'Closed Lost' opportunity records with linked RLIs
    And Opportunities records exist related via opportunities link to *Account_1:
      | *name | lead_source | opportunity_type  |
      | Opp_2 | Cold Call   | Existing Business |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_2:
      | *name | date_closed | likely_case | sales_stage |
      | RLI_3 | now         | 1000        | Closed Lost |
      | RLI_4 | now         | 2000        | Closed Lost |

    # Enable 'Closed Won' and 'Closed Lost' columns in Opportunities Tile View
    When I drag-n-drop column header items on "Opportunities" module in #TileViewSettings view:
      | sourceItem  | destination | position |
      | Closed Won  | white_list  | 2        |
      | Closed Lost | white_list  | 4        |

    # Navigate to Opportunities module
    When I choose Opportunities in modules menu
    # Navigate to Opportunities > Tile View
    And I select TileView in #OpportunitiesList.FilterView
    # Switch to Opportunities by Time tab
    And I select opportunitiesByTime tab in #OpportunitiesTileView view

    # Verify tile is located in the correct column
    Then I verify the [*Opp_1] records are under "now" column in #OpportunitiesTileView view
    # Move tile from current month to the next month
    When I drag *Opp_1 tile to "now + 30d" column in #OpportunitiesTileView view
    # Verify that tile with all closed RLIs is not moved to a new column
    Then I verify the [*Opp_1] records are under "now" column in #OpportunitiesTileView view

    # Open RLI subpanel in the opportunity record view and verify Expected Close Date of each RLI
    When I select *Opp_1 in #OpportunitiesTileView
    When I open the revenuelineitems subpanel on #Opp_1Record view
    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value |
      | name        | RLI_1 |
      | date_closed | now   |
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value |
      | name        | RLI_2 |
      | date_closed | now   |

    # Navigate to Opportunities module
    When I choose Opportunities in modules menu
    # Navigate to Opportunities > Tile View
    And I select TileView in #OpportunitiesList.FilterView
    # Switch to Opportunities by Stage tab
    And I select opportunitiesByStage tab in #OpportunitiesTileView view

    # Verify tile is located in the correct column
    Then I verify the [*Opp_1] records are under "Closed Won" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are under "Closed Lost" column in #OpportunitiesTileView view
    # Move tile from one sales stage to another
    When I drag *Opp_1 tile to "Qualification" column in #OpportunitiesTileView view
    When I drag *Opp_2 tile to "Qualification" column in #OpportunitiesTileView view
    # Verify that tile with all closed RLIs is not moved to a new column
    Then I verify the [*Opp_1] records are under "Closed Won" column in #OpportunitiesTileView view
    Then I verify the [*Opp_2] records are under "Closed Lost" column in #OpportunitiesTileView view

    # Disable 'Closed Won' and 'Closed Lost' columns in Opportunities Tile View
    When I drag-n-drop column header items on "Opportunities" module in #TileViewSettings view:
      | sourceItem  | destination | position |
      | Closed Won  | black_list  | 0        |
      | Closed Lost | black_list  | 1        |


  @cases_tileView @CommentLog @pr @AT-341 @SS-282
  Scenario Outline: Cases > Tile View > Support for Comment Log field in Tile View
    Given Accounts records exist:
      | *name |
      | Acc_1 |
    And Cases records exist related via Cases link to *Acc_1:
      | *   | name        | status | priority |
      | C_1 | My New Case | New    | P2       |

    # Create custom user
    Given I create custom user "user"

    # Update Case record to add Comment Log & Status fields to tile body
    When I update "Cases" module in #TileViewSettings view with the following settings:
      | table_header | tile_options_header | tile_options_body   | records_per_column |
      |              |                     | Comment Log, Status |                    |

    # Update the case's Comment Log field
    When I choose Cases in modules menu
    When I select *C_1 in #CasesList.ListView
    When I click Edit button on #C_1Record header
    When I provide input for #C_1Record.RecordView view
      | commentlog  |
      | <message_1> |
    When I click Save button on #C_1Record header
    When I close alert

    # Navigate to Cases > Tile View
    When I choose Cases in modules menu
    When I select VisualPipeline in #CasesList.FilterView

    # Verify value of comment log field in tile view body
    Then I verify *C_1 tile field values in #CasesPipelineView view
      | value                |
      | My New Case          |
      | Acc_1                |
      | Medium               |
      | <admin>: <message_1> |
      | New                  |

    # Logout from Admin and Login as another user
    When I logout
    When I use account "user"
    When I open Cases view and login

    # Update the case's Comment Log field
    When I select *C_1 in #CasesList.ListView
    When I click Edit button on #C_1Record header
    When I provide input for #C_1Record.RecordView view
      | commentlog  |
      | <message_2> |
    When I click Save button on #C_1Record header
    When I close alert

    # Navigate to Cases > Tile View
    When I choose Cases in modules menu
    When I select VisualPipeline in #CasesList.FilterView

    # Verify value of comment log field in tile view body
    Then I verify *C_1 tile field values in #CasesPipelineView view
      | value                                     |
      | My New Case                               |
      | Acc_1                                     |
      | Medium                                    |
      | <admin>: <message_1>, <user>: <message_2> |
      | New                                       |

    # Logout from Admin and Login as another user
    When I logout
    When I use default account
    When I open Cases view and login

    # Update the case's Comment Log field
    When I select *C_1 in #CasesList.ListView
    When I click Edit button on #C_1Record header
    When I provide input for #C_1Record.RecordView view
      | commentlog  |
      | <message_3> |
    When I click Save button on #C_1Record header
    When I close alert

    # Navigate to Cases > Tile View
    When I choose Cases in modules menu
    When I select VisualPipeline in #CasesList.FilterView

    # Verify value of comment log field in tile view body
    Then I verify *C_1 tile field values in #CasesPipelineView view
      | value                                                           |
      | My New Case                                                     |
      | Acc_1                                                           |
      | Medium                                                          |
      | <admin>: <message_1>, <user>: <message_2>, <admin>: <message_3> |
      | New                                                             |

    # Update Case record to remove Comment Log field from tile body
    When I update "Cases" module in #TileViewSettings view with the following settings:
      | table_header | tile_options_header | tile_options_body | records_per_column |
      |              |                     | Comment Log~r     |                    |

    # Verify value of comment log field in removed from tile body
    Then I verify *C_1 tile field values in #CasesPipelineView view
      | value       |
      | My New Case |
      | Acc_1       |
      | Medium      |
      | New         |

    # Update Case record to remove Status field from tile body - restore default set of fields
    When I update "Cases" module in #TileViewSettings view with the following settings:
      | table_header | tile_options_header | tile_options_body | records_per_column |
      |              |                     | Status~r          |                    |

    Examples:
      | admin         | user           | message_1                    | message_2                                        | message_3                   |
      | Administrator | user userLName | Please update case info ASAP | Case was successfully resolved today! Sugar won! | Congratulation on a big Win |
