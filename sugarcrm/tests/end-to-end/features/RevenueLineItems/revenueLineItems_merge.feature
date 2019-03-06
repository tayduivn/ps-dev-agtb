# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @rli_merge @job6
Feature: Merge verification: As a Sugar user, I need to be able to merge RLI records in RLI list view

  Background:
    Given I am logged in

  @pr
  Scenario: RLI > List View > Merge > Verify that records are merged successfully
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage        | quantity |
      | RLI_1 | 2020-10-19T19:20:22+00:00 | 300         | 350       | Negotiation/Review | 5        |
      | RLI_2 | 2019-10-19T19:20:22+00:00 | 500         | 550       | Prospecting        | 1        |
    And Opportunities records exist:
      | *name |
      | Opp_1 |

    # Link RLI to Opportunity record
    When I perform mass update of RevenueLineItems [*RLI_1, *RLI_2] with the following values:
      | fieldName        | value |
      | opportunity_name | Opp_1 |

    # Perform records merge with no prior edit
    When I perform merge of RevenueLineItems [*RLI_1, *RLI_2] records

    # Verify that records are successfully merged
    Then I should not see [*RLI_2] on RevenueLineItems list view
    And RevenueLineItems *RLI_1 should have the following values:
      | fieldName        | value      |
      | name             | RLI_1      |
      | likely_case      | $300.00    |
      | best_case        | $350.00    |
      | date_closed      | 10/19/2020 |
      | opportunity_name | Opp_1      |


  Scenario: RLIs > List View > Try to merge RLI records linked to different opportunities > Verify alert message is correct
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2021-01-01T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    And Opportunities records exist related via opportunities link to *RLI_1:
      | *name |
      | Opp_1 |

    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage    | quantity |
      | RLI_2 | 2018-10-19T19:20:22+00:00 | 250        | 350         | 450       | Needs Analysis | 2        |
    And Opportunities records exist related via opportunities link to *RLI_2:
      | *name |
      | Opp_2 |

    # Try to merge invalid number of records
    When I perform out of range merge of RevenueLineItems [*RLI_1, *RLI_2] records

    # Verify properties of the displayed alert
    Then I verify and close alert
      | type    | message                                                                                                                 |
      | warning | Warning One or more of the records you've selected can not be merged together as they belong to different Opportunities |
