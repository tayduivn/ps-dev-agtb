# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@quote_config @job3
Feature: Quotes Configuration

  Background:
    Given I am logged in

  @quote_config @pr
  Scenario: Quotes > Quotes Config > Make Changes in Quote Configuration drawer

    # Create quote record
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2025-10-19T19:20:22+00:00  | Negotiation |
    And Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    # Create a product bundle
    And ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name   |
      | Group_1 |
    # Add QLI
    And Products records exist related via products link:
      | *name | discount_price | discount_amount | quantity |
      | QLI_1 | 100            | 2               | 2        |
      | QLI_2 | 200            | 2               | 3        |

    # Make changes to quote header in Quotes Config
    When I go to "Quotes/config" url
    When I select fields in #QuotesConfigDrawer.IntelligencePane
      | fieldName                  |
      | date_quote_expected_closed |

    # Make changes to worksheet columns in Quotes Config
    When I expand Worksheet_Columns on #QuotesConfigDrawer.Accordion
    When I select fields in #QuotesConfigDrawer.IntelligencePane
      | fieldName  |
      | cost_price |

    # Make changes to quote footer in Quotes Config
    When I expand Grand_Totals_Footer on #QuotesConfigDrawer.Accordion
    When I select fields in #QuotesConfigDrawer.IntelligencePane
      | fieldName         |
      | deal_tot          |
      | deal_tot_usdollar |

    # Click Save
    When I click Save button on #QuotesConfigDrawer header
    When I wait for 10 seconds

    # Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    # Verify QLI table Header is updated
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName                  | value      |
      | date_quote_expected_closed | 10/19/2025 |
    # Verify QLI table Footer is updated
    Then I verify fields on QLI total footer on #Quote_3Record view
      | fieldName         | value  |
      | deal_tot          | $16.00 |
      | deal_tot_usdollar | $16.00 |

    # Edit quote record
    When I click Edit button on #Quote_3Record header
    When I provide input for #Quote_3Record.RecordView view
      | date_quote_expected_closed |
      | 01/01/2026                 |
    When I click Save button on #Quote_3Record header
    When I close alert

    # Verify QLI table Header is updated
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName                  | value      |
      | date_quote_expected_closed | 01/01/2026 |

    # Add new QLI records with custom field 'cost_price'
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | cost_price | quantity | product_template_name | discount_price | discount_amount | discount_select |
      | Test1 | 100        | 2.00     | New QLI               | 100            | 2.00            | $ US Dollar     |
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert

    # Verify QLI table Footer is updated
    Then I verify fields on QLI total footer on #Quote_3Record view
      | fieldName         | value  |
      | deal_tot          | $18.00 |
      | deal_tot_usdollar | $18.00 |

    # Restore Defaults settings in quote config
    When I go to "Quotes/config" url
    When I Restore Defaults on #QuotesConfigDrawer.Accordion
    When I expand Worksheet_Columns on #QuotesConfigDrawer.Accordion
    When I select fields in #QuotesConfigDrawer.IntelligencePane
      | fieldName  |
      | cost_price |
    When I expand Grand_Totals_Footer on #QuotesConfigDrawer.Accordion
    When I select fields in #QuotesConfigDrawer.IntelligencePane
      | fieldName         |
      | deal_tot          |
      | deal_tot_usdollar |
    # Click Save
    When I click Save button on #QuotesConfigDrawer header
    When I close alert
