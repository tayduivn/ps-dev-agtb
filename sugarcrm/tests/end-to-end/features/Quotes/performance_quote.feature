# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@quotes
Feature: Quotes module performance testing

  Background:
    Given I use default account
    Given I launch App


    # TITLE:  Verify performance of Copy Quote functionality - Scenario 1
    #
    # STEPS:
    # 1. Generate quote record with 7 groups and 7 QLIs in each group
    # 2. Navigate to quote record view
    # 3. Start timer
    # 4. Initiate Copy Quote process by click on related menu item in the quote record view Actions menu
    # 5. Stop Timer and verify
    # 6. Name and save the newly created quote record
    # 7. Check the alert message properties
    # 8. Verify amounts in Grand Total header of QLI table

  @performance_Copy_Quote
  Scenario: Quotes > Copy Quote with 50 QLIs (7 by 7 + 1 ) > Performance Measure
    # 1. Generate quote record linked to the account
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |

    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |

    # Create a product bundles
    Given 8 ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name           |
      | Group_{{index}} |

    # Add 7 QLIs to the group
    Given 7 Products records exist related via products link to *Group_1:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    # Add 7 QLIs to the group
    Given 7 Products records exist related via products link to *Group_2:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    # Add 7 QLIs to the group
    Given 7 Products records exist related via products link to *Group_3:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    # Add 7 QLIs to the group
    Given 7 Products records exist related via products link to *Group_4:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    # Add 7 QLIs to the group
    Given 7 Products records exist related via products link to *Group_5:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    # Add 7 QLIs to the group
    Given 7 Products records exist related via products link to *Group_6:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    # Add 7 QLIs to the group
    Given 7 Products records exist related via products link to *Group_7:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    # Add 1 QLI to the group
    Given Products records exist related via products link to *Group_8:
      | *name | discount_price | discount_amount | quantity |
      | QLI_1 | 100            | 2               | 2        |

    Given I open about view and login
    # 2. Navigate to Quotes record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I open actions menu in #Quote_3Record

    # 3. Start timer
    When I start timer

    # 4. Initiate Copy Quote process by click on related menu item
    When I choose Copy from actions menu in #Quote_3Record

    # 5. Stop Timer and verify
    When I stop timer and verify
      | max   |
      | 18100 |

    # 6. Name and save the newly created quote record
    When I provide input for #QuotesRecord.HeaderView view
      | *name   |
      | Quote_4 |
    When I click Save button on #QuotesRecord header

    # 7. Check the alert message properties
    Then I check alert
      | type    | message                                             |
      | Success | Success You successfully created the quote Quote_4. |
    When I close alert
    Then I should see #QuotesRecord view

    # 8. Verify amounts in Grand Total header of QLI table
    Then I verify fields on QLI total header on #QuotesRecord view
      | fieldName | value         |
      | deal_tot  | 2.00% $200.00 |
      | new_sub   | $9,800.00     |
      | tax       | $0.00         |
      | shipping  | $0.00         |
      | total     | $9,800.00     |



    # TITLE:  Verify performance of Copy Quote functionality - Scenario 2
    #
    # STEPS:
    # 1. Generate quote record with 1 group and 50 QLIs in the group
    # 2. Navigate to quote record view
    # 3. Start timer
    # 4. Initiate Copy Quote process by click on related menu item in the quote record view Actions menu
    # 5. Stop Timer and verify
    # 6. Name and save the newly created quote record
    # 7. Check the alert message properties
    # 8. Verify amounts in Grand Total header of QLI table

  @performance_Copy_Quote
  Scenario: Quotes > Copy Quote with 50 QLIs (1 by 50 ) > Performance Measure
    # 1. Generate quote record linked to the account
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |

    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |

    # Create a product bundles
    Given ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name   |
      | Group_1 |

    # Add 50 QLIs to the group
    Given 50 Products records exist related via products link to *Group_1:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    Given I open about view and login
    # 2. Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I open actions menu in #Quote_3Record

    # 3. Start timer
    When I start timer

    # 4. Initiate Copy Quote process by click on related menu item
    When I choose Copy from actions menu in #Quote_3Record

    # 5. Stop Timer and verify
    When I stop timer and verify
      | max   |
      | 18000 |

    # 6. Name and save the newly created quote record
    When I provide input for #QuotesRecord.HeaderView view
      | *name   |
      | Quote_4 |
    When I click Save button on #QuotesRecord header

    # 7. Check the alert message properties
    Then I check alert
      | type    | message                                             |
      | Success | Success You successfully created the quote Quote_4. |
    When I close alert
    Then I should see #QuotesRecord view

    # 8. Verify amounts in Grand Total header of QLI table
    Then I verify fields on QLI total header on #QuotesRecord view
      | fieldName | value         |
      | deal_tot  | 2.00% $200.00 |
      | new_sub   | $9,800.00     |
      | tax       | $0.00         |
      | shipping  | $0.00         |
      | total     | $9,800.00     |



    # TITLE:  Verify performance of Copy Quote functionality - Scenario 3
    #
    # STEPS:
    # 1. Generate quote record with 50 group-less QLIs
    # 2. Navigate to quote record view
    # 3. Start timer
    # 4. Initiate Copy Quote process by click on related menu item in the quote record view Actions menu
    # 5. Stop Timer and verify
    # 6. Name and save the newly created quote record
    # 7. Check the alert message properties
    # 8. Verify amounts in Grand Total header of QLI table

  @performance_Copy_Quote
  Scenario: Quotes > Copy Quote with 50 QLIs (50 groupless) > Performance Measure
      # 1. Generate quote record with 50 group-less QLIs
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |

    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |

      # Create a group-less product bundle record
    Given ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name   | default_group |
      | Group_1 | true          |

      # Add 50 QLIs
    Given 50 Products records exist related via products link to *Group_1:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    Given I open about view and login
    # 2. Navigate to Quotes record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I open actions menu in #Quote_3Record

    # 3. Start timer
    When I start timer

    # 4. Initiate Copy Quote process by click on related menu item
    When I choose Copy from actions menu in #Quote_3Record

    # 5. Stop Timer and verify
    When I stop timer and verify
      | max   |
      | 20000 |

    # 6. Name and save the newly created quote record
    When I provide input for #QuotesRecord.HeaderView view
      | *name   |
      | Quote_4 |
    When I click Save button on #QuotesRecord header

    # 7. Check the alert message properties
    Then I check alert
      | type    | message                                             |
      | Success | Success You successfully created the quote Quote_4. |
    When I close alert
    Then I should see #QuotesRecord view

    # 8. Verify amounts in Grand Total header of QLI table
    Then I verify fields on QLI total header on #QuotesRecord view
      | fieldName | value         |
      | deal_tot  | 2.00% $200.00 |
      | new_sub   | $9,800.00     |
      | tax       | $0.00         |
      | shipping  | $0.00         |
      | total     | $9,800.00     |



    # TITLE:  Verify performance of Copy Quote functionality - Scenario 4
    #
    # STEPS:
    # 1. Generate quote record with 75 QLIs  ( 5 group of 10 + 1 group of 25)
    # 2. Navigate to quote record view
    # 3. Start timer
    # 4. Initiate Copy Quote process by click on related menu item in the quote record view Actions menu
    # 5. Stop Timer and verify
    # 6. Name and save the newly created quote record
    # 7. Check the alert message properties
    # 8. Verify amounts in Grand Total header of QLI table

  @performance_Copy_Quote
  Scenario: Quotes > Copy Quote with 75 QLIs (5 gr. by 10 qlis + 1 gr by 25 qlis) > Performance Measure
      # 1. Generate quote record with 75 QLIs  ( 5 group of 10 + 1 group of 25)
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |

      # Create a product bundles
    Given 6 ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name           |
      | Group_{{index}} |

    # Add 10 QLIs to the group
    Given 10 Products records exist related via products link to *Group_1:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    # Add 10 QLIs to the group
    Given 10 Products records exist related via products link to *Group_2:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    # Add 10 QLIs to the group
    Given 10 Products records exist related via products link to *Group_3:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    # Add 10 QLIs to the group
    Given 10 Products records exist related via products link to *Group_4:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    # Add 10 QLIs to the group
    Given 10 Products records exist related via products link to *Group_5:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    # Add 25 QLIs to the group
    Given 25 Products records exist related via products link to *Group_6:
      | *name         | discount_price | discount_amount | quantity |
      | QLI_{{index}} | 100            | 2               | 2        |

    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |

    Given I open about view and login
    # 2. Navigate to Quotes record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I open actions menu in #Quote_3Record

    # 3. Start timer
    When I start timer

    # 4. Initiate Copy Quote process by click on related menu item in the quote record view Actions menu
    When I choose Copy from actions menu in #Quote_3Record

    # 5. Stop Timer and verify
    When I stop timer and verify
      | max   |
      | 30000 |

    # 6. Name and save the newly created quote record
    When I provide input for #QuotesRecord.HeaderView view
      | *name   |
      | Quote_4 |
    When I click Save button on #QuotesRecord header

    # 7. Check the alert message properties
    Then I check alert
      | type    | message                                             |
      | Success | Success You successfully created the quote Quote_4. |
    When I close alert
    Then I should see #QuotesRecord view

    # 8. Verify amounts in Grand Total header of QLI table
    Then I verify fields on QLI total header on #QuotesRecord view
      | fieldName | value         |
      | deal_tot  | 2.00% $300.00 |
      | new_sub   | $14,700.00    |
      | tax       | $0.00         |
      | shipping  | $0.00         |
      | total     | $14,700.00    |
