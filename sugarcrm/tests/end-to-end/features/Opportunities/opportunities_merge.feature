# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @opportunities_merge @job3 @ent-only
Feature: Opportunities > List View > Merge

  Background:
    Given I am logged in

  @pr
  Scenario: Opportunities > Merge > Verify that calculated fields are properly updated after opportunities records merge
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

    And Accounts records exist:
      | *name |
      | Acc_1 |

    # Link Opportunity records to the Account
    When I perform mass update of Opportunities [*Opp_1, *Opp_2] with the following values:
      | fieldName    | value |
      | account_name | Acc_1 |

    # Perform merge operation with some fields updated prior to the merge
    When I perform merge of Opportunities [*Opp_1, *Opp_2] records with the following changes:
      | fieldName | value              |
      | name      | Merged Opportunity |
      | next_step | Merge Records      |

    # Verify that records are successfully merged
    Then I should not see [*Opp_2] on Opportunities list view
    And Opportunities *Opp_1 should have the following values:
      | fieldName    | value              |
      | name         | Merged Opportunity |
      | account_name | Acc_1              |
      | amount       | $650.00            |
      | best_case    | $850.00            |
      | worst_case   | $450.00            |
      | date_closed  | 01/01/2021         |
      | sales_status | In Progress        |
      | next_step    | Merge Records      |

    # Verify number of records in RLI subpanel after the merge
    When I open the revenuelineitems subpanel on #Opp_1Record view
    Then I verify number of records in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems is 2


  @merge @job4 @ci-excluded
  Scenario: Opportunities Only mode > Merge > Verify that the merge process is completed successfully

    # Switch to Opportunities Only mode
    Given I configure Opportunities mode
      | name            | value         |
      | opps_view_by    | Opportunities |
      | opps_close_date | earliest      |

    # Generate 2 opportunities
    Given Opportunities records exist:
      | *name | worst_case | amount | best_case | date_closed               | opportunity_type  | lead_source       | description       |
      | Opp_1 | 100        | 200    | 300       | 2020-10-19T19:20:22+00:00 | Existing Business | Cold Call         | Opp_1 description |
      | Opp_2 | 150        | 250    | 350       | 2020-10-20T19:20:22+00:00 | New Business      | Existing Customer | Opp_2 description |

    # Link Account to each opportunity
    And Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    And Accounts records exist related via accounts link to *Opp_2:
      | *name |
      | Acc_2 |

    # Perform merge of opportunities in Opportunities Only mode
    When I perform merge of Opportunities [*Opp_1, *Opp_2] records

    # Verify that records are successfully merged
    Then I should not see [*Opp_2] on Opportunities list view
    And Opportunities *Opp_1 should have the following values:
      | fieldName   | value      |
      | name        | Opp_1      |
      | best_case   | $300.00    |
      | amount      | $200.00    |
      | worst_case  | $100.00    |
      | date_closed | 10/19/2020 |

    # Switch back to Opportunities + RLI mode
    When I configure Opportunities mode
      | name            | value            |
      | opps_view_by    | RevenueLineItems |
      | opps_close_date | latest           |
