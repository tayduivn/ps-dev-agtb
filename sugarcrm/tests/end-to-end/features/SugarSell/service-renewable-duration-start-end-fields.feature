# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @sugarsell @job6
Feature: New fields for sugar sell Service, Renewable, Service Duration, Service Start Date, Service End Date.

  Background:
    Given I use default account
    Given I launch App

  # Enable/Change license type to sugar sell
  Scenario: User Profile > Change license type
    Given I open about view and login
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Sell" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value                        |
      | Sugar Enterprise, Sugar Sell |


  Scenario: Revenue Line Items > Record View > Verify new Service related fields
    Given I open about view and login

    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
    And Opportunities records exist related via opportunities link to *A_1:
      | *     | name  |
      | Opp_1 | Opp_1 |

    When I go to "RevenueLineItems" url
    # Creation of RevenueLineItems and input of new fields e.g., years
    When I click Create button on #RevenueLineItemsList header
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | *  | name               |
      | R1 | RevenueLineItems_1 |
    When I click show more button on #RevenueLineItemsDrawer view
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | *  | opportunity_name | likely_case | date_closed | service | renewable | service_duration_value | service_duration_unit | service_start_date |
      | R1 | Opp_1            | 5.00        | 12/05/2020  | true    | true      | 1                      | Year(s)               | 11/05/2020         |
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert
    # Verification of RevenueLineItems and input of new fields e.g., years
    When I select *R1 in #RevenueLineItemsList.ListView
    Then I should see #R1Record view
    Then I click show more button on #R1Record view
    Then I verify fields on #R1Record
      | fieldName              | value              |
      | name                   | RevenueLineItems_1 |
      | service                | true               |
      | renewable              | true               |
      | service_duration_value | 1                  |
      | service_start_date     | 11/05/2020         |
      | service_duration_unit  | Year(s)            |
      | service_end_date       | 11/04/2021         |

    When I go to "RevenueLineItems" url
    # Creation of RevenueLineItems and input of new fields e.g., months
    When I click Create button on #RevenueLineItemsList header
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | *  | name               |
      | R2 | RevenueLineItems_2 |
    When I click show more button on #RevenueLineItemsDrawer view
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | *  | opportunity_name | likely_case | date_closed | service | renewable | service_duration_value | service_duration_unit | service_start_date |
      | R2 | Opp_1            | 5.00        | 12/05/2020  | true    | true      | 1                      | Month(s)              | 11/05/2020         |
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert
    # Verification of RevenueLineItems and input of new fields e.g., months
    When I select *R2 in #RevenueLineItemsList.ListView
    Then I should see #R2Record view
    Then I click show more button on #R2Record view
    Then I verify fields on #R2Record
      | fieldName              | value              |
      | name                   | RevenueLineItems_2 |
      | service                | true               |
      | renewable              | true               |
      | service_duration_value | 1                  |
      | service_start_date     | 11/05/2020         |
      | service_duration_unit  | Month(s)           |
      | service_end_date       | 12/04/2020         |

    When I go to "RevenueLineItems" url
    # Creation of RevenueLineItems and input of new fields e.g., days
    When I click Create button on #RevenueLineItemsList header
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | *  | name               |
      | R3 | RevenueLineItems_3 |
    When I click show more button on #RevenueLineItemsDrawer view
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | *  | opportunity_name | likely_case | date_closed | service | renewable | service_duration_value | service_duration_unit | service_start_date |
      | R3 | Opp_1            | 5.00        | 12/05/2020  | true    | true      | 1                      | Day(s)                | 11/05/2020         |
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert
    # Verification of RevenueLineItems and input of new fields e.g., days
    When I select *R3 in #RevenueLineItemsList.ListView
    Then I should see #R3Record view
    Then I click show more button on #R3Record view
    Then I verify fields on #R3Record
      | fieldName              | value              |
      | name                   | RevenueLineItems_3 |
      | service                | true               |
      | renewable              | true               |
      | service_duration_value | 1                  |
      | service_start_date     | 11/05/2020         |
      | service_duration_unit  | Day(s)             |
      | service_end_date       | 11/05/2020         |

  Scenario: Products > Record View > New Fields
    Given I open about view and login

    When I go to "Products" url
    # Creation of Products and input of new fields e.g., years
    When I click Create button on #ProductsList header
    When I provide input for #ProductsDrawer.HeaderView view
      | *  | name      |
      | P1 | Product_1 |
    When I click show more button on #ProductsDrawer view
    When I provide input for #ProductsDrawer.RecordView view
      | *  | service | renewable | service_duration_value | service_duration_unit | service_start_date |
      | P1 | true    | true      | 1                      | Year(s)               | 11/05/2020         |
    When I click Save button on #ProductsDrawer header
    When I close alert
    Then I should see *P1 in #ProductsList.ListView
    # Verification of Products and input of new fields e.g., years
    When I select *P1 in #ProductsList.ListView
    Then I should see #P1Record view
    Then I click show more button on #P1Record view
    Then I verify fields on #P1Record
      | fieldName              | value      |
      | name                   | Product_1  |
      | service                | true       |
      | renewable              | true       |
      | service_duration_value | 1          |
      | service_start_date     | 11/05/2020 |
      | service_duration_unit  | Year(s)    |
      | service_end_date       | 11/04/2021 |

    When I go to "Products" url
    # Creation of Products and input of new fields e.g., months
    When I click Create button on #ProductsList header
    When I provide input for #ProductsDrawer.HeaderView view
      | *  | name      |
      | P2 | Product_2 |
    When I click show more button on #ProductsDrawer view
    When I provide input for #ProductsDrawer.RecordView view
      | *  | service | renewable | service_duration_value | service_duration_unit | service_start_date |
      | P2 | true    | true      | 1                      | Month(s)              | 11/05/2020         |
    When I click Save button on #ProductsDrawer header
    When I close alert
    Then I should see *P2 in #ProductsList.ListView
    # Verification of Products and input of new fields e.g., months
    When I select *P2 in #ProductsList.ListView
    Then I should see #P2Record view
    Then I click show more button on #P2Record view
    Then I verify fields on #P2Record
      | fieldName              | value      |
      | name                   | Product_2  |
      | service                | true       |
      | renewable              | true       |
      | service_duration_value | 1          |
      | service_start_date     | 11/05/2020 |
      | service_duration_unit  | Month(s)   |
      | service_end_date       | 12/04/2020 |

    When I go to "Products" url
    # Creation of Products and input of new fields e.g., days
    When I click Create button on #ProductsList header
    When I provide input for #ProductsDrawer.HeaderView view
      | *  | name      |
      | P3 | Product_3 |
    When I click show more button on #ProductsDrawer view
    When I provide input for #ProductsDrawer.RecordView view
      | *  | service | renewable | service_duration_value | service_duration_unit | service_start_date |
      | P3 | true    | true      | 1                      | Day(s)                | 11/05/2020         |
    When I click Save button on #ProductsDrawer header
    When I close alert
    Then I should see *P3 in #ProductsList.ListView
    # Verification of Products and input of new fields e.g., days
    When I select *P3 in #ProductsList.ListView
    Then I should see #P3Record view
    Then I click show more button on #P3Record view
    Then I verify fields on #P3Record
      | fieldName              | value      |
      | name                   | Product_3  |
      | service                | true       |
      | renewable              | true       |
      | service_duration_value | 1          |
      | service_start_date     | 11/05/2020 |
      | service_duration_unit  | Day(s)     |
      | service_end_date       | 11/05/2020 |

  Scenario: ProductTemplates > Record View > New Fields
    Given I open about view and login

    When I go to "ProductTemplates" url
    # Creation of Products and input of new fields e.g., years
    When I click Create button on #ProductTemplatesList header
    When I provide input for #ProductTemplatesDrawer.HeaderView view
      | *  | name      |
      | P1 | Product_1 |
    When I click show more button on #ProductTemplatesDrawer view
    When I provide input for #ProductTemplatesDrawer.RecordView view
      | *  | cost_price | discount_price | list_price | service | renewable | service_duration_value | service_duration_unit |
      | P1 | 10.00      | 5.00           | 25.00      | true    | true      | 1                      | Year(s)               |
    When I click Save button on #ProductTemplatesDrawer header
    When I close alert
    Then I should see *P1 in #ProductTemplatesList.ListView
    # Verification of Products and input of new fields e.g., years
    When I select *P1 in #ProductTemplatesList.ListView
    Then I should see #P1Record view
    Then I click show more button on #P1Record view
    Then I verify fields on #P1Record
      | fieldName              | value     |
      | name                   | Product_1 |
      | service                | true      |
      | renewable              | true      |
      | service_duration_value | 1         |
      | service_duration_unit  | Year(s)   |

    When I go to "ProductTemplates" url
    # Creation of Products and input of new fields e.g., months
    When I click Create button on #ProductTemplatesList header
    When I provide input for #ProductTemplatesDrawer.HeaderView view
      | *  | name      |
      | P2 | Product_2 |
    When I click show more button on #ProductTemplatesDrawer view
    When I provide input for #ProductTemplatesDrawer.RecordView view
      | *  | cost_price | discount_price | list_price | service | renewable | service_duration_value | service_duration_unit |
      | P2 | 10.00      | 5.00           | 25.00      | true    | true      | 1                      | Month(s)              |
    When I click Save button on #ProductTemplatesDrawer header
    When I close alert
    Then I should see *P2 in #ProductTemplatesList.ListView
    # Verification of Products and input of new fields e.g., months
    When I select *P2 in #ProductTemplatesList.ListView
    Then I should see #P2Record view
    Then I click show more button on #P2Record view
    Then I verify fields on #P2Record
      | fieldName              | value     |
      | name                   | Product_2 |
      | service                | true      |
      | renewable              | true      |
      | service_duration_value | 1         |
      | service_duration_unit  | Month(s)  |

    When I go to "ProductTemplates" url
    # Creation of Products and input of new fields e.g., days
    When I click Create button on #ProductTemplatesList header
    When I provide input for #ProductTemplatesDrawer.HeaderView view
      | *  | name      |
      | P3 | Product_3 |
    When I click show more button on #ProductTemplatesDrawer view
    When I provide input for #ProductTemplatesDrawer.RecordView view
      | *  | cost_price | discount_price | list_price | service | renewable | service_duration_value | service_duration_unit |
      | P3 | 10.00      | 5.00           | 25.00      | true    | true      | 1                      | Day(s)                |
    When I click Save button on #ProductTemplatesDrawer header
    When I close alert
    Then I should see *P3 in #ProductTemplatesList.ListView
    # Verification of Products and input of new fields e.g., days
    When I select *P3 in #ProductTemplatesList.ListView
    Then I should see #P3Record view
    Then I click show more button on #P3Record view
    Then I verify fields on #P3Record
      | fieldName              | value     |
      | name                   | Product_3 |
      | service                | true      |
      | renewable              | true      |
      | service_duration_value | 1         |
      | service_duration_unit  | Day(s)    |

  # Disable/Change license type from sugar sell
  Scenario: User Profile > Change license type
    Given I open about view and login
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Sell" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value            |
      | Sugar Enterprise |
