# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@job4
Feature: Pipeline View feature

  Background:
    Given I am logged in

  @pipelineView_opportunities
  Scenario: Opportunities > Pipeline View
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    When I choose Opportunities in modules menu
    When I select VisualPipeline in #OpportunitiesList.FilterView
    Then I should be redirected to "Opportunities/pipeline" route

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
      | *name | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI1  | 04/19/2019  | 300       | Qualification | 5        | 2000        |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Switch tab in opportunities pipeline view
    When I select pipelineByStatus tab in #OpportunitiesPipelineView view

    # Verify column headers in Pipeline By Status tab
    Then I verify pipeline column headers in #OpportunitiesPipelineView view
      | value       |
      | New         |
      | In Progress |
      | Closed Won  |
      | Closed Lost |

    # Switch tab in opportunities pipeline view
    When I select pipelineByTime tab in #OpportunitiesPipelineView view

    # Filter specific record
    When I search for "CreateOpportunityTest" in #OpportunitiesList.FilterView view

    # Verify record's information displayed in the tile
    Then I verify *Opp_1 tile field values in #OpportunitiesPipelineView view
      | value                 |
      | CreateOpportunityTest |
      | Acc_1                 |
      | 04/19/2019            |
      | $2,000.00             |

    # Delete record in pipeline view
    When I delete *Opp_1 in #OpportunitiesPipelineView view

    # Navigate to the list view
    When I select ListView in #OpportunitiesList.FilterView


  @pipelineView_leads
  Scenario: Leads > Pipeline View
    Given Leads records exist:
      | *      | first_name | last_name | account_name   | title                      | email                        |
      | Lead_1 | Rafael     | Nadal     | Rafa's Account | Professional Tennis Player | lead_1@example.net (primary) |
    When I choose Leads in modules menu
    When I select VisualPipeline in #LeadsList.FilterView
    Then I verify pipeline column headers in #LeadsPipelineView view
      | value      |
      | New        |
      | Assigned   |
      | In Process |
      | Converted  |
      | Recycled   |
      | Dead       |

    # Filter specific record
    When I search for "Rafael Nadal" in #LeadsList.FilterView view

    # Verify record's tile information
    Then I verify *Lead_1 tile field values in #LeadsPipelineView view
      | value          |
      | Rafael Nadal   |
      | Rafa's Account |
