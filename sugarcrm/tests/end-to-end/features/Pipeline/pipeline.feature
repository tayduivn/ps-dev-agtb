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

  @pipelineView_opportunities @pr
  Scenario: Tile View > Opportunities by Sales Stage
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    When I choose Opportunities in modules menu
    When I select VisualPipeline in #OpportunitiesList.FilterView
    Then I should be redirected to "Opportunities/pipeline" route

    # Switch tab in opportunities pipeline view
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
      | *     | name                  |
      | Opp_1 | CreateOpportunityTest |
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
    When I search for "CreateOpportunityTest" in #OpportunitiesList.FilterView view

    # Verify record's information displayed in the tile
    Then I verify *Opp_1 tile field values in #OpportunitiesPipelineView view
      | value                 |
      | CreateOpportunityTest |
      | Acc_1                 |
      | 04/19/2020            |
      | $2,000.00             |

    # Delete record in pipeline view
    When I delete *Opp_1 in #OpportunitiesPipelineView view

    # Switch tab in opportunities pipeline view
    When I select pipelineByTime tab in #OpportunitiesPipelineView view

    # Navigate to the list view
    When I select ListView in #OpportunitiesList.FilterView
