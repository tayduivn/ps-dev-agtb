# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@Opportunities @job4
Feature: Renewal Opp > Auto-generate Renewal Opportunity when original renewable opportunity is closed as 'Closed Won'

  Background:
    Given I am logged in

  @user_profile
  Scenario: User Profile > Change license type
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Sell" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value                        |
      | Sugar Enterprise, Sugar Sell |
    When I click on Cancel button on #UserProfile

  @renewal_opportunity
  Scenario: Renewal Opportunity > Auto-generate Renewal Opportunity when original renewable service opp is closed as 'Closed Won'
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
    And Opportunities records exist related via opportunities link to *A_1:
      | *     | name  |
      | Opp_1 | Opp_1 |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | worst_case | likely_case | best_case | sales_stage         | quantity | service | service_start_date | service_duration_value | service_duration_unit | renewable |
      | RLI_1 | now         | 1000       | 2000        | 3000      | Prospecting         | 1        | true    | 2020-01-01         | 2                      | year                  | true      |
      | RLI_2 | now         | 1000       | 2000        | 3000      | Closed Lost         | 1        | true    | 2020-01-02         | 2                      | year                  | false     |
      | RLI_3 | now         | 1000       | 2000        | 3000      | Needs Analysis      | 1        | true    | 2020-01-03         | 2                      | year                  | true      |
      | RLI_4 | now         | 1000       | 2000        | 3000      | Perception Analysis | 1        | false   |                    |                        |                       |           |

    # Change Sales Stage of RLIs to "Close Won"
    When I perform mass update of RevenueLineItems [*RLI_1, *RLI_3, *RLI_4] with the following values:
      | fieldName   | value      |
      | sales_stage | Closed Won |

    # Verify that new renewal RLIs are auto-generated
    Then I verify number of records in #RevenueLineItemsList.ListView is 6

    # Since renewal opportunity has the same name as original, rename original opportunity to differentiate orginal and renewal
    When I edit *Opp_1 record in Opportunities list view with the following values:
      | fieldName | value          |
      | name      | Original_Opp_1 |

    # Assign ID for newly generated renewal opportunity
    When I filter for the Opportunities record *OppRenewal named "Opp_1"

    # Verify that Renewal Opportunity is generated properly
    When I select *OppRenewal in #OpportunitiesList.ListView
    When I click show more button on #OppRenewalRecord view
    Then I verify fields on #OppRenewalRecord.HeaderView
      | fieldName | value                 |
      | name      | Opp_1                 |
      | renewal   | January, 2022 Renewal |
    Then I verify fields on #OppRenewalRecord.RecordView
      | fieldName           | value          |
      | date_closed         | 01/03/2022     |
      | sales_status        | In Progress    |
      | amount              | $4,000.00      |
      | renewal_parent_name | Original_Opp_1 |

    # Add another opportunity
    Given Opportunities records exist related via opportunities link to *A_1:
      | *name |
      | Opp_2 |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_2:
      | *name | date_closed | worst_case | likely_case | best_case | sales_stage | quantity | service | service_start_date | service_duration_value | service_duration_unit | renewable |
      | RLI_5 | now         | 1400       | 1500        | 1600      | Prospecting | 1        | true    | 2020-02-05         | 2                      | year                  | true      |

    # Link newly created opportunity to the original opportunity
    When I choose Opportunities in modules menu
    When I select *Opp_2 in #OpportunitiesList.ListView
    When I click show more button on #Opp_2Record view
    When I click Edit button on #Opp_2Record header
    When I provide input for #Opp_2Record.RecordView view
      | renewal_parent_name |
      | Original_Opp_1      |
    When I click Save button on #Opp_2Record header
    When I close alert

    # Close newly created opportunity by closing linked RLI record
    When I edit *RLI_5 record in RevenueLineItems list view with the following values:
      | fieldName   | value      |
      | sales_stage | Closed Won |

    When I choose Opportunities in modules menu

    # Verify number of opportunities records
    Then I verify number of records in #OpportunitiesList.ListView is 3

    # Verify that Renewal Opportunity is updated correctly
    When I select *OppRenewal in #OpportunitiesList.ListView
    When I click show more button on #OppRenewalRecord view
    Then I verify fields on #OppRenewalRecord.HeaderView
      | fieldName | value                  |
      | name      | Opp_1                  |
      | renewal   | February, 2022 Renewal |
    Then I verify fields on #OppRenewalRecord.RecordView
      | fieldName           | value          |
      | date_closed         | 02/05/2022     |
      | sales_status        | In Progress    |
      | amount              | $5,500.00      |
      | renewal_parent_name | Original_Opp_1 |

    # Delete renewal opportunity -  this will also erase of all RLI records linked to renewal opportunity
    When I delete *OppRenewal record in Opportunities list view
    Then I should not see [*OppRenewal] on Opportunities list view

  @user_profile
  Scenario: User Profile > Change license type
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Sell" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value            |
      | Sugar Enterprise |
    When I click on Cancel button on #UserProfile
