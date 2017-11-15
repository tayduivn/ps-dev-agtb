# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@quotes
Feature: Quotes module verification

  Background:
    Given I use default account
    Given I launch App

    # TITLE:  Verify that Tax amount is calculated correctly when Tax is applied to a Quote record
    #
    # STEPS:
    # 1. Generate quote record with one group and 2 QLIs linked to the account
    # 2. Generate 2 tax rates
    # 3. Navigate to quote record view
    # 4. Select first tax rate and save
    # 5. Verify that tax amount in QLI Grand Total bar is calculated properly
    # 6. Select second Tax Rate
    # 7. Verify that tax amount in QLI Grand Total bar is calculated properly

  @quote_calculate_taxRate
  Scenario: Quotes > Verify that Tax amount is calculated correctly when Tax is applied to a Quote record
      # 1. Generate quote record with one group and 2 QLIs linked to the account
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
      # Create a product bundle
    Given ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name   |
      | Group_1 |
      # Add QLI
    Given Products records exist related via products link:
      | *name | discount_price | discount_amount | quantity |
      | QLI_1 | 100            | 2               | 2        |
      | QLI_2 | 200            | 2               | 3        |
      # 2. Generate 2 tax rates
    Given TaxRates records exist:
      | *name | list_order | status | value |
      | Tax_1 | 4          | Active | 10.00 |
      | Tax_2 | 5          | Active | 5.00  |
    Given I open about view and login
    When I choose Quotes in modules menu
    # 3. Navigate to quote record view
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    # 4. Select first tax rate
    When I click Edit button on #Quote_3Record header
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | taxrate_name |
      | Tax_1        |
    When I click Save button on #QuotesRecord header
    When I close alert
      # 5. Verify that tax amount in QLI Grand Total bar is calculated properly
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value   |
      | deal_tot  | 2.00%   |
      | new_sub   | $784.00 |
      | tax       | $78.40  |
      | shipping  | $0.00   |
      | total     | $862.40 |
      # 6. Select second tax rate
    When I click Edit button on #Quote_3Record header
    When I provide input for #Quote_3Record.RecordView view
      | taxrate_name |
      | Tax_2        |
    When I click Save button on #QuotesRecord header
    When I close alert
      # 7. Verify that tax amount in QLI Grand Total bar is calculated properly
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value   |
      | deal_tot  | 2.00%   |
      | new_sub   | $784.00 |
      | tax       | $39.20  |
      | shipping  | $0.00   |
      | total     | $823.20 |
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view


    # TITLE:  Verify that Tax amount is calculated correctly if one of the QLIs is non-Taxable
    #
    # STEPS:
    # 1. Generate quote record with no Groups and no QLIs
    # 2. Generate two products in Product Catalog: "Prod_1" is Non-Taxable, "Prod_2" is Taxable
    # 3. Generate Tax Rate
    # 4. Navigate to quote record view
    # 5. Add New non-Taxable Line Item
    # 6. Add new Taxable Line Item from "Prod_2"
    # 7. Verify that QLIs are added properly
    # 8. Verify that numbers in Grand Totals bar are calculated properly
    # 9. Apply Tax
    # 10. Verify that tax amount in QLI Grand Total bar is calculated properly

  @quote @add_new_QLI_from_Not_Taxable_Product
  Scenario: Quotes > Verify that Tax amount is calculated correctly if one of the QLIs is non-Taxable
    # 1. Generate quote record with no Groups and no QLIs
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    # 2. Generate two products in Product Catalog: "prod_1" is Non-taxable, "prod_2" is Taxable
    Given ProductTemplates records exist:
      | *name  | discount_price | cost_price | list_price | quantity | mft_part_num                 | tax_class   |
      | Prod_1 | 100            | 100        | 100        | 10       | B.H. Edwards Inc 72868XYZ987 | Non-Taxable |
      | Prod_2 | 100            | 100        | 100        | 10       | B.H. Edwards Inc 72868XYZ989 | Taxable     |
    # 3. Generate Tax Rate
    Given TaxRates records exist:
      | *name | list_order | status | value |
      | Tax_1 | 4          | Active | 10.00 |
    Given I open about view and login
    # 4. Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    # 5. Add new non-Taxable Line Item from "Prod_1"
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | quantity | product_template_name | discount_price | discount_amount |
      | test1 | 2.00     | Prod_1                | 100            | 2.00            |
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert
    # 6. Add new Taxable Line Item from "Prod_2"
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | quantity | product_template_name | discount_price | discount_amount |
      | test2 | 2.00     | Prod_2                | 100            | 2.00            |
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert
    # 7. Verify that QLIs are added properly
    Then I verify fields on #test1QLIRecord
      | fieldName      | value   |
      | discount_price | $100.00 |
      | total_amount   | $196.00 |
    Then I verify fields on #test2QLIRecord
      | fieldName      | value   |
      | discount_price | $100.00 |
      | total_amount   | $196.00 |
    # 8. Verify numbers in Grand Totals bar are calculated properly
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value   |
      | deal_tot  | 2.00%   |
      | new_sub   | $392.00 |
      | tax       | $0.00   |
      | shipping  | $0.00   |
      | total     | $392.00 |
    # 9. Apply Tax
    When I click Edit button on #Quote_3Record header
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | taxrate_name |
      | Tax_1        |
    When I click Save button on #QuotesRecord header
    When I close alert
    # 10. Verify that tax amount in QLI Grand Total bar is calculated properly
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value   |
      | deal_tot  | 2.00%   |
      | new_sub   | $392.00 |
      | tax       | $19.60  |
      | shipping  | $0.00   |
      | total     | $411.60 |


    # TITLE:  Verify that total amounts are recalculated correctly when currency of the quote is changed
    #
    # STEPS:
    # 1. Generate quote record with one group and 2 QLIs linked to the account
    # 2. Add a new custom currency
    # 3. Navigate to quote record view
    # 4. Change the currency of the quote to a newly created currency
    # 5. Verify that numbers in QLI Grand Total bar are converted properly
    # 6. Add new QLI record
    # 7. Verify that added QLI info is correct
    # 8. Verify that numbers in QLI Grand Total bar are updated properly
    # 9. Return back to original USD currency
    # 10. Add new QLI record
    # 11. Verify that added QLI info is correct
    # 12. Verify that numbers in QLI Grand Total bar are updated properly

  @quote @change_quote_currency
  Scenario: Quotes > Verify that total amounts are recalculated correctly when currency of the quote is changed
      # 1. Generate quote record with one group and 2 QLIs linked to the account
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
      # Create a product bundle
    Given ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name   |
      | Group_1 |
      # Add QLIs
    Given Products records exist related via products link:
      | *name | discount_price | discount_amount | quantity |
      | QLI_1 | 100            | 2               | 2        |
      | QLI_2 | 200            | 2               | 3        |
    Given I open about view and login
      # 2. Add a new custom currency
    When I go to "Currencies" url
    When I click Create button on #CurrenciesList header
    When I provide input for #CurrenciesDrawer.HeaderView view
      | name  |
      | Alex2 |
    When I provide input for #CurrenciesDrawer.RecordView view
      | symbol | conversion_rate |
      | T      | 0.5             |
    When I click Save button on #CurrenciesDrawer header
    When I close alert
      # 3. Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I click Edit button on #Quote_3Record header
      # 4. Change the currency of the quote
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | currency_id |
      | T ()        |
    When I click Save button on #QuotesRecord header
    When I close alert
      # 5. Verify that numbers in QLI Grand Total bar are converted properly
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value   |
      | deal_tot  | 2.00%   |
      | new_sub   | T392.00 |
      | tax       | T0.00   |
      | shipping  | T0.00   |
      | total     | T392.00 |
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
      # 6. Add new QLI record
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | quantity | product_template_name | discount_price | discount_amount |
      | test1 | 2.00     | New QLI               | 100            | 2.00            |
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert
      # 7. Verify that added QLI info is correct
    Then I verify fields on #test1QLIRecord
      | fieldName      | value          |
      | discount_price | T100.00$200.00 |
      | total_amount   | T196.00        |
      # 8. Verify that numbers in QLI Grand Total bar are updated properly
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value   |
      | deal_tot  | 2.00%   |
      | new_sub   | T588.00 |
      | tax       | T0.00   |
      | shipping  | T0.00   |
      | total     | T588.00 |
    When I click Edit button on #Quote_3Record header
      # 9. Change the currency of the quote back to USD
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | currency_id |
      | $ (USD)     |
    When I click Save button on #QuotesRecord header
    When I close alert
    # 10. Add another new QLI record
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | quantity | product_template_name | discount_price | discount_amount |
      | test2 | 2.00     | New QLI_2             | 100            | 2.00            |
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert
      # 11. Verify that added QLI info is correct
    Then I verify fields on #test2QLIRecord
      | fieldName      | value   |
      | discount_price | $100.00 |
      | total_amount   | $196.00 |
     # 12. Verify that numbers in QLI Grand Total bar are updated properly
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value     |
      | deal_tot  | 2.00%     |
      | new_sub   | $1,372.00 |
      | tax       | $0.00     |
      | shipping  | $0.00     |
      | total     | $1,372.00 |
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
