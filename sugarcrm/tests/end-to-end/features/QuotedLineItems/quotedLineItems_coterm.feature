# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@qli @job8 @ent-only @pr
Feature: QLI module verification - coterm

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
      | value      |
      | Sugar Sell |
    When I click on Cancel button on #UserProfile


  @SS-737 @SS-688
  Scenario Outline: Set duration of coterm QLIs to make End Date remain fixed
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |

    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | service | renewable | description            |
      | Pur_1 | Purchase 1 | true    | true      | This is great purchase |

    And PurchasedLineItems records exist related via purchasedlineitems link to *Pur_1:
      | *     | name  | revenue | date_closed | quantity | service_start_date | service_end_date | service | renewable | discount_price |
      | PLI_1 | PLI_1 | 2000    | 2030-05-31  | 1.00     | 2030-06-01         | 2032-05-31       | true    | true      | 2000           |

    # Generate QLI record
    Given Products records exist:
      | *name | discount_price | cost_price | list_price |
      | P_1   | 100            | 100        | 100        |

    # Mark QLI as a service
    When I go to "Products" url
    When I select *P_1 in #ProductsList.ListView
    When I click Edit button on #P_1Record header
    When I click show more button on #P_1Record view
    When I provide input for #P_1Record.RecordView view
      | service |
      | true    |
    When I click Save button on #P_1Record header
    When I close alert

    # Verify that default duration is set to 1 year
    Then I verify fields on #P_1Record.RecordView
      | fieldName              | value   |
      | service                | true    |
      | add_on_to_name         |         |
      | service_duration_value | 1       |
      | service_duration_unit  | Year(s) |
      | service_start_date     | now     |
      | renewable              | false   |

    # Link PLI to QLI in the 'Add on to' field and and set 'service_start_date'
    When I click Edit button on #P_1Record header
    When I provide input for #P_1Record.RecordView view
      | add_on_to_name | service_start_date |
      | PLI_1          | 06/01/2030         |
    When I click Save button on #P_1Record header
    When I close alert

    # Verify that service duration is update properly
    Then I verify fields on #P_1Record.RecordView
      | fieldName              | value              |
      | add_on_to_name         | PLI_1              |
      | service_duration_value | 2                  |
      | service_duration_unit  | Year(s)            |
      | service_start_date     | 06/01/2030         |
      | service_end_date       | <service_end_date> |
      | renewable              | false              |

    # Edit RLI - move service start date by 1 day
    When I click Edit button on #P_1Record header
    When I provide input for #P_1Record.RecordView view
      | service_start_date |
      | 06/02/2030         |
    When I click Save button on #P_1Record header
    When I close alert

    # Verify service duration is updated properly
    Then I verify fields on #P_1Record.RecordView
      | fieldName              | value              |
      | service_duration_value | 730                |
      | service_duration_unit  | Day(s)             |
      | service_start_date     | 06/02/2030         |
      | service_end_date       | <service_end_date> |

    # Edit RLI - move service start date by 1 year
    When I click Edit button on #P_1Record header
    When I provide input for #P_1Record.RecordView view
      | service_start_date |
      | 06/01/2031         |
    When I click Save button on #P_1Record header
    When I close alert

    # Verify service duration is updated properly
    Then I verify fields on #P_1Record.RecordView
      | fieldName              | value              |
      | service_duration_value | 1                  |
      | service_duration_unit  | Year(s)            |
      | service_start_date     | 06/01/2031         |
      | service_end_date       | <service_end_date> |

    # Edit RLI - make service start day after service end day
    When I click Edit button on #P_1Record header
    When I provide input for #P_1Record.RecordView view
      | service_start_date |
      | 06/01/2032         |
    When I click Save button on #P_1Record header

    # Verify record could not be saved and error message is displayed
    Then I check alert
      | type  | message                                            |
      | Error | Error Please resolve any errors before proceeding. |
    When I close alert

    # Edit RLI - make service start day the same as service end day
    When I provide input for #P_1Record.RecordView view
      | service_start_date |
      | <service_end_date> |
    When I click Save button on #P_1Record header
    When I close alert

    # Verify service duration is updated properly
    Then I verify fields on #P_1Record.RecordView
      | fieldName              | value              |
      | service_duration_value | 1                  |
      | service_duration_unit  | Day(s)             |
      | service_start_date     | <service_end_date> |
      | service_end_date       | <service_end_date> |

    Examples:
      | service_end_date |
      | 05/31/2032       |

  @user_profile
  Scenario: User Profile > Change license type
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Enterprise" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value            |
      | Sugar Enterprise |
    When I click on Cancel button on #UserProfile
