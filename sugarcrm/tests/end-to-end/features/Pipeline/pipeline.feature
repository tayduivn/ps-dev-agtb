# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@job4
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
    When I select VisualPipeline in #OpportunitiesList.FilterView
    Then I should be redirected to "Opportunities/pipeline" route

    # Switch to Opportunities by Sales Stage tab
    When I select pipelineByStage tab in #OpportunitiesPipelineView view

    # Verify column headers in Pipeline By Stage tab
    Then I verify pipeline column headers in #OpportunitiesPipelineView view
      | value                |
      | Prospecting          |
      | Qualification        |
      | Needs Analysis       |
      | Value Proposition    |
      | Id. Decision Makers  |
      | Perception Analysis  |
      | Proposal/Price Quote |
      | Negotiation/Review   |

    # Create New opportunity while in pipeline view
    When I click pipelineCreate button on #OpportunitiesList header
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
    Then I verify *Opp_1 tile field values in #OpportunitiesPipelineView view
      | value           |
      | New Opportunity |
      | Acc_1           |
      | 04/19/2020      |
      | $2,000.00       |

    # Verify column the opportunity tile appears under
    Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesPipelineView view

    # Verify state of the tile delete button
    Then I verify *Opp_1 tile delete button state in #OpportunitiesPipelineView view
      | Disabled |
      | false    |

    # Add second RLI record to opportunity
    When I select *Opp_1 in #OpportunitiesPipelineView
    When I create_new record from revenuelineitems subpanel on #Opp_1Record view
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | *name |
      | RLI2  |
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | date_closed | sales_stage    | likely_case |
      | 05/20/2020  | Needs Analysis | 2000        |
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert

    # Navigate to Pipeline View
    When I choose Opportunities in modules menu
    When I select VisualPipeline in #OpportunitiesList.FilterView
    # Switch to Opportunities by Sales Stage tab
    When I select pipelineByStage tab in #OpportunitiesPipelineView view

    # Verify record's information displayed in the tile
    Then I verify *Opp_1 tile field values in #OpportunitiesPipelineView view
      | value           |
      | New Opportunity |
      | Acc_1           |
      | 05/20/2020      |
      | $4,000.00       |

    # Verify column the opportunity tile appears under
    Then I verify the [*Opp_1] records are under "Needs Analysis" column in #OpportunitiesPipelineView view

    # Add Closed Won RLI and Closed Lost RLI to opportunity through mass update
    When I perform mass update of RevenueLineItems [*RLI3, *RLI4] with the following values:
      | fieldName        | value           |
      | opportunity_name | New Opportunity |

    # Navigate to Pipeline View
    When I choose Opportunities in modules menu
    When I select VisualPipeline in #OpportunitiesList.FilterView
    # Switch to Opportunities by Sales Stage tab
    When I select pipelineByStage tab in #OpportunitiesPipelineView view

    # Verify record's information displayed in the tile
    Then I verify *Opp_1 tile field values in #OpportunitiesPipelineView view
      | value           |
      | New Opportunity |
      | Acc_1           |
      | 12/12/2020      |
      | $4,300.00       |

    # Verify state of the tile delete button
    Then I verify *Opp_1 tile delete button state in #OpportunitiesPipelineView view
      | Disabled |
      | true     |

    # Verify column the opportunity tile appears under
    Then I verify the [*Opp_1] records are under "Needs Analysis" column in #OpportunitiesPipelineView view

    # remove Closed Won RLI and Closed Lost RLI from opportunity through mass update
    When I perform mass update of RevenueLineItems [*RLI3, *RLI4] with the following values:
      | fieldName        | value |
      | opportunity_name | Opp_2 |

    # Navigate to Pipeline View
    When I choose Opportunities in modules menu
    When I select VisualPipeline in #OpportunitiesList.FilterView
    # Switch to Opportunities by Sales Stage tab
    When I select pipelineByStage tab in #OpportunitiesPipelineView view

    # Verify column the opportunity tile appears under
    Then I verify the [*Opp_1] records are under "Needs Analysis" column in #OpportunitiesPipelineView view
    # Closed Won and closed lost columns have to be activated first
    # Then I verify the [*Opp_2] records are under "Closed Won" column in #OpportunitiesPipelineView view

    # Delete record in pipeline view
    When I delete *Opp_1 in #OpportunitiesPipelineView view

    # Switch tab in opportunities pipeline view
    When I select pipelineByTime tab in #OpportunitiesPipelineView view

    # Navigate to the list view
    When I select ListView in #OpportunitiesList.FilterView


  @cases_tileView
  Scenario: Cases > Tile View
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    # Navigate to Cases > Tile View
    When I choose Cases in modules menu
    When I select VisualPipeline in #CasesList.FilterView
    Then I should be redirected to "Cases/pipeline" route

    # Verify column headers in Pipeline By Stage tab
    Then I verify pipeline column headers in #CasesPipelineView view
      | value         |
      | New           |
      | Assigned      |
      | Closed        |
      | Pending Input |
      | Rejected      |
      | Duplicate     |

    # Create New Case while in Tile View
    When I click pipelineCreate button on #CasesList header
    When I provide input for #CasesDrawer.HeaderView view
      | *  | name   |
      | C2 | Case 2 |
    When I provide input for #CasesDrawer.RecordView view
      | *  | account_name | priority | status   |
      | C2 | Acc_1        | Medium   | Assigned |
    When I click Save button on #CasesDrawer header
    When I close alert

    # Verify column the case tile appears under
    Then I verify the [*C2] records are under "Assigned" column in #CasesPipelineView view

    # Verify record's information displayed in the tile
    Then I verify *C2 tile field values in #CasesPipelineView view
      | value  |
      | Case 2 |
      | Acc_1  |
      | Medium |

    # Change Case Status
    When I select *C2 in #CasesPipelineView
    When I click Edit button on #C2Record header
    When I provide input for #C2Record.RecordView view
      | *  | priority | status |
      | C2 | High     | Closed |
    When I click Save button on #C2Record header
    When I close alert

    When I choose Cases in modules menu
    When I select VisualPipeline in #CasesList.FilterView

    # Verify column the case tile appears under
    Then I verify the [*C2] records are under "Closed" column in #CasesPipelineView view

    # Verify record's information displayed in the tile
    Then I verify *C2 tile field values in #CasesPipelineView view
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
    When I select VisualPipeline in #TasksList.FilterView
    Then I should be redirected to "Tasks/pipeline" route

    # Verify column headers in Pipeline By Stage tab
    Then I verify pipeline column headers in #TasksPipelineView view
      | value         |
      | Not Started   |
      | In Progress   |
      | Completed     |
      | Pending Input |
      | Deferred      |

    # Create New Task while in Tile View
    When I click pipelineCreate button on #TasksList header
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
    Then I verify the [*T1] records are under "Not Started" column in #TasksPipelineView view

    # Verify record's information displayed in the tile
    Then I verify *T1 tile field values in #TasksPipelineView view
      | value              |
      | Task 1             |
      | cFirst cLast       |
      | Acc_1              |
      | 05/10/2020 02:00pm |

    # Change Task Status
    When I select *T1 in #TasksPipelineView
    When I click Edit button on #T1Record header
    When I provide input for #T1Record.RecordView view
      | *  | priority | status      | parent_name       | date_due           |
      | C2 | Medium   | In Progress | Opportunity,Opp_1 | 06/10/2020-12:00pm |
    When I click Save button on #T1Record header
    When I close alert

    When I choose Tasks in modules menu
    When I select VisualPipeline in #TasksList.FilterView

    # Verify column the case tile appears under
    Then I verify the [*T1] records are under "In Progress" column in #TasksPipelineView view

    # Verify record's information displayed in the tile
    Then I verify *T1 tile field values in #TasksPipelineView view
      | value              |
      | Task 1             |
      | cFirst cLast       |
      | Opp_1              |
      | 06/10/2020 12:00pm |
