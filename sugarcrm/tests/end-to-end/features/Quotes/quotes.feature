# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@quotes @job5
Feature: Quotes module E2E testing

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
      | fieldName | value        |
      | deal_tot  | 2.00% $16.00 |
      | new_sub   | $784.00      |
      | tax       | $78.40       |
      | shipping  | $0.00        |
      | total     | $862.40      |
      # 6. Select second tax rate
    When I click Edit button on #Quote_3Record header
    When I provide input for #Quote_3Record.RecordView view
      | taxrate_name |
      | Tax_2        |
    When I click Save button on #QuotesRecord header
    When I close alert
      # 7. Verify that tax amount in QLI Grand Total bar is calculated properly
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value        |
      | deal_tot  | 2.00% $16.00 |
      | new_sub   | $784.00      |
      | tax       | $39.20       |
      | shipping  | $0.00        |
      | total     | $823.20      |
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
      | fieldName | value       |
      | deal_tot  | 2.00% $8.00 |
      | new_sub   | $392.00     |
      | tax       | $0.00       |
      | shipping  | $0.00       |
      | total     | $392.00     |
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
      | fieldName | value       |
      | deal_tot  | 2.00% $8.00 |
      | new_sub   | $392.00     |
      | tax       | $19.60      |
      | shipping  | $0.00       |
      | total     | $411.60     |



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
      | fieldName | value       |
      | deal_tot  | 2.00% T8.00 |
      | new_sub   | T392.00     |
      | tax       | T0.00       |
      | shipping  | T0.00       |
      | total     | T392.00     |
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
      | fieldName      | value   |
      | discount_price | T100.00 |
      | total_amount   | T196.00 |
    # 8. Verify that numbers in QLI Grand Total bar are updated properly
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value        |
      | deal_tot  | 2.00% T12.00 |
      | new_sub   | T588.00      |
      | tax       | T0.00        |
      | shipping  | T0.00        |
      | total     | T588.00      |
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
      | fieldName | value        |
      | deal_tot  | 2.00% $28.00 |
      | new_sub   | $1,372.00    |
      | tax       | $0.00        |
      | shipping  | $0.00        |
      | total     | $1,372.00    |
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view



    # TITLE:  Verify that calculations are updated properly after user edits and deletes existing QLI/Comment record
    #
    # STEPS:
    # 1. Generate quote record linked to the account
    # 2. Navigate to quote record view

  @quote @edit_delete_existing_QLI_record @edit_delete_existing_Comment_record
  Scenario: Quotes > Verify that user can edit/delete existing QLI and Comment record
    # 1. Generate quote record
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    # 2. Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view

    # Part 1:  Verify that existing QLI record can be edited and deleted

    # Steps:
    # 1. Add new QLI records
    # 2. Verify all fields in QLI Grand Total bar are calculated properly
    # 3. Edit existing QLI and cancel
    # 4. Verify that no values are changed after QLI editing is canceled
    # 5. Edit existing QLI and save
    # 6. Verify that new values are saved and QLI Total is updated
    # 7. Verify all fields in QLI Grand Total bar are recalculated
    # 8. Delete existing QLI and cancel
    # 9. Verify that Grand Total value did not change
    # 10. Delete existing QLi and confirm
    # 11. Verify that Grant Total value is updated after QLI is deleted

    # 1. Add new QLI records
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | quantity | product_template_name | discount_price | discount_amount | discount_select |
      | Test1 | 2.00     | New QLI               | 100            | 2.00            | $ US Dollar     |
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert
    # 2. Verify all fields in QLI Grand Total bar are calculated properly
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value       |
      | deal_tot  | 1.00% $2.00 |
      | new_sub   | $198.00     |
      | tax       | $0.00       |
      | shipping  | $0.00       |
      | total     | $198.00     |
    # 3. Edit existing QLI and cancel
    When I choose editLineItem on #Test1QLIRecord
    When I provide input for #Test1QLIRecord view
      | quantity | discount_price | discount_amount | discount_select | product_template_name |
      | 4        | 150.00         | 4.00            | $ US Dollar     | New Name              |
    When I click on cancel button on QLI #Test1QLIRecord record
    # 4. Verify that no values are changed after QLI editing is canceled
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value       |
      | deal_tot  | 1.00% $2.00 |
      | new_sub   | $198.00     |
      | tax       | $0.00       |
      | shipping  | $0.00       |
      | total     | $198.00     |
    # 5. Edit existing QLI and save
    When I choose editLineItem on #Test1QLIRecord
    When I provide input for #Test1QLIRecord view
      | quantity | discount_price | discount_amount | product_template_name | discount_select |
      | 4        | 150.00         | 4.00            | New QLI EDited        | % Percent       |
    When I click on save button on QLI #Test1QLIRecord record
    # 6. Verify that new values are saved and QLI Total is updated
    Then I verify fields on #Test1QLIRecord
      | fieldName             | value          |
      | product_template_name | New QLI EDited |
      | quantity              | 4.00           |
      | discount_price        | $150.00        |
      | discount_amount       | 4.00%          |
    # 7. Verify all fields in QLI Grand Total bar are recalculated
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value        |
      | deal_tot  | 4.00% $24.00 |
      | new_sub   | $576.00      |
      | tax       | $0.00        |
      | shipping  | $0.00        |
      | total     | $576.00      |
    # 8. Delete existing QLI and cancel
    When I choose deleteLineItem on #Test1QLIRecord
    When I Cancel confirmation alert
    # 9. Verify that Grand Total value did not change
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value   |
      | total     | $576.00 |
    # 10. Delete existing QLi and confirm
    When I choose deleteLineItem on #Test1QLIRecord
    When I Confirm confirmation alert
    When I close alert
    # 11. Verify that Grant Total value is updated after QLI is deleted
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value |
      | total     | $0.00 |

    # Part 2:  Verify that existing comment record can be edited and deleted
    #
    # STEPS:
    # 1. Add New Comment and verify
    # 2. Edit new comment and Cancel editing
    # 3. Verify that original comment did not change
    # 4. Edit new comment and save changes
    # 5. Verify that changes to the comment are saved
    # 6. Delete new comment > Cancel
    # 7. Delete new comment > Confirm

    # 1. Add New Comment and Verify
    When I choose createComment on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.CommentRecord view
      | *        | description   |
      | Comment1 | Alex Nisevich |
    When I click on save button on Comment #Quote_3Record.QliTable.CommentRecord record
    When I close alert
    Then I verify fields on #Comment1CommentRecord
      | fieldName   | value         |
      | description | Alex Nisevich |
    # 2. Edit new comment and Cancel editing
    When I choose editLineItem on #Comment1CommentRecord
    When I provide input for #Comment1CommentRecord view
      | description |
      | test        |
    When I click on cancel button on Comment #Comment1CommentRecord record
    # 3. Verify that original comment did not change
    Then I verify fields on #Comment1CommentRecord
      | fieldName   | value         |
      | description | Alex Nisevich |
    # 4. Edit new comment and save changes
    When I choose editLineItem on #Comment1CommentRecord
    When I provide input for #Comment1CommentRecord view
      | description     |
      | Ruslan Golovach |
    When I click on save button on Comment #Comment1CommentRecord record
    When I close alert
    # 5. Verify that changes to the comment are saved
    Then I verify fields on #Comment1CommentRecord
      | fieldName   | value           |
      | description | Ruslan Golovach |
    # 6. Delete new comment > Cancel
    When I choose deleteLineItem on #Comment1CommentRecord
    When I Cancel confirmation alert
    # 7. Delete new comment > Confirm
    When I choose deleteLineItem on #Comment1CommentRecord
    When I Confirm confirmation alert
    When I close alert

    # TITLE:  Verify that user can edit/delete existing group
    #
    # STEPS:
    # 1. Generate quote record linked to the account
    # 2. Navigate to quote record view
    # 3. Add first group and verify that group is added successfully
    # 4. Add second group and verify that group is added successfully
    # 5. Edit second group and cancel. Verify.
    # 6. Edit second group and save. Verify
    # 7. Delete first group and cancel. Verify
    # 8. Delete first group and confirm. Verify

  @quote @edit_delete_existing_group
  Scenario: Quotes > Record View > QLI Table > Add/Edit/Delete Group > Cancel/Save
    # 1. Generate quote record linked to the account
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    # 2. Navigate to Quotes record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    # 3. Add first group and verify that group is added successfully
    When I choose createGroup on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | *        | name          |
      | MyGroup1 | Alex Nisevich |
    When I click on save button on Group #MyGroup1GroupRecord record
    When I close alert
    Then I verify fields on #MyGroup1GroupRecord
      | fieldName | value         |
      | name      | Alex Nisevich |
    # 4. Add second group and verify that group is added successfully
    When I choose createGroup on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | *        | name            |
      | MyGroup2 | Ruslan Golovach |
    When I click on save button on Group #MyGroup2GroupRecord record
    When I close alert
    Then I verify fields on #MyGroup2GroupRecord
      | fieldName | value           |
      | name      | Ruslan Golovach |
    # 5. Edit second group and cancel. Verify.
    When I choose editGroup on #MyGroup2GroupRecord
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | *        | name      |
      | MyGroup2 | New Group |
    When I click on cancel button on Group #MyGroup2GroupRecord record
    Then I verify fields on #MyGroup2GroupRecord
      | fieldName | value           |
      | name      | Ruslan Golovach |
    # 6. Edit second group and save. Verify
    When I choose editGroup on #MyGroup2GroupRecord
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | *        | name      |
      | MyGroup2 | New Group |
    When I click on save button on Group #MyGroup2GroupRecord record
    Then I verify fields on #MyGroup2GroupRecord
      | fieldName | value     |
      | name      | New Group |
    # 7. Delete first group and cancel. Verify
    When I choose deleteGroup on #MyGroup1GroupRecord
    When I Cancel confirmation alert
    Then I verify fields on #MyGroup2GroupRecord
      | fieldName | value     |
      | name      | New Group |
    # 8. Delete first group and confirm. Verify
    When I choose deleteGroup on #MyGroup1GroupRecord
    When I Confirm confirmation alert



    # TITLE:  Verify that calculations are updated properly after editing QLI in QLI record view
    #
    # STEPS:
    # 1. Generate quote record with one group and 2 QLIs linked to the account
    # 2. Navigate to quote record view
    # 3. Click on QLI name link in QLI table to open the record in QLI record view and verify the data
    # 4. Edit QLI record and save
    # 5. Return back to quotes record view and verify the QLI record is updated
    # 6. Verify all calculated fields in QLI Grand Total bar are recalculated properly

  @quote @click_on_QLI_link
  Scenario: Quotes > Verify that user can edit existing QLI in QLI record view
    # 1. Generate quote record with one group and 2 QLIs linked to the account
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name   |
      | Group_1 |
      # Add QLIs
    Given Products records exist related via products link:
      | *     | name  | discount_price | discount_amount | quantity |
      | Test1 | QLI_1 | 100            | 2               | 2        |
    Given I open about view and login
    # 2. Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    # 3. Click on QLI name link in QLI table to open the record in QLI record view and verify the data
    When I click product_template_name field on #Test1QLIRecord view
    Then I should see #ProductsRecord view
    Then I verify fields on #ProductsRecord.HeaderView
      | fieldName | value |
      | name      | QLI_1 |
    Then I verify fields on #ProductsRecord.RecordView
      | fieldName       | value   |
      | quantity        | 2.00    |
      | discount_amount | 2.00%   |
      | discount_price  | $100.00 |
    # 4. Edit QLI record and save
    When I click Edit button on #Test1Record header
    When I provide input for #Test1Record.RecordView view
      | quantity | discount_amount |
      | 40.00    | 5.00            |
    When I provide input for #Test1Record.HeaderView view
      | name         |
      | QLI_1 Edited |
    When I click Save button on #Test1Record header
    When I close alert
    # 5. Return back to quotes record view and verify the QLI record is updated
    When I click quote_name field on #Test1Record view
    Then I verify fields on #Test1QLIRecord
      | fieldName             | value        |
      | quantity              | 40.00        |
      | product_template_name | QLI_1 Edited |
      | discount_amount       | 5.00%        |
      | discount_price        | $100.00      |
      | total_amount          | $3,800.00    |
    # 6. Verify all calculated fields in QLI Grand Total bar are recalculated properly
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value         |
      | deal_tot  | 5.00% $200.00 |
      | new_sub   | $3,800.00     |
      | tax       | $0.00         |
      | shipping  | $0.00         |
      | total     | $3,800.00     |



    # TITLE:  Verify that the "Cost" and "List Price" fields editable in QLI record view/list view
    #
    # STEPS:
    # 1. Generate quote record with one QLI
    # 2. Navigate to quote record view
    # 3. Click on QLI name link in QLI table to open the record in QLI record view and verify the data
    # 4. Edit List Price and Cost fields and save
    # 5. Verify that List Price and Cost fields are updated in QLI record view
    # 6. Navigate to QLI list view
    # 7. Edit Cost field in the list view and save
    # 8. Verify that Cost field is updated in the list view

  @quote @SFA-5165 @ZT-22-partial
  Scenario: Quotes > Verify that the "Cost" and "List Price" fields editable in QLI record view/list view
    # 1. Generate quote record with one QLI
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name   |
      | Group_1 |
      # Add QLIs
    Given Products records exist related via products link:
      | *     | name  | discount_price | discount_amount | quantity |
      | Test1 | QLI_1 | 100            | 2               | 2        |
    Given I open about view and login
    # 2. Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    # 3. Click on QLI name link in QLI table to open the record in QLI record view and verify the data
    When I click product_template_name field on #Test1QLIRecord view
    Then I should see #ProductsRecord view
    # 4. Edit List Price and Cost fields and save
    When I click Edit button on #Test1Record header
    When I provide input for #Test1Record.RecordView view
      | list_price | cost_price |
      | 200        | 300        |
    When I click Save button on #Test1Record header
    When I close alert
    # 5.Verify that List Price and Cost fields are updated
    Then I verify fields on #Test1Record.RecordView
      | fieldName  | value   |
      | list_price | $200.00 |
      | cost_price | $300.00 |
    # 6. Navigate to QLI list view
    When I go to "Products" url
    Then I should see #ProductsList.ListView view
    Then I should see *Test1 in #ProductsList.ListView
    # 7. Edit Cost field in the list view and save
    When I click on Edit button for *Test1 in #ProductsList.ListView
    When I set values for *Test1 in #ProductsList.ListView
      | fieldName  | value |
      | cost_price | 400   |
    When I click on Save button for *Test1 in #ProductsList.ListView
    # 8. Verify that Cost field is updated in the list view
    Then I verify fields for *Test1 in #ProductsList.ListView
      | fieldName  | value   |
      | cost_price | $400.00 |



    # TITLE:  Verify that comment changes are NOT lost when adding new comment during editing
    #
    # STEPS:
    # 1. Generate quote record linked to the account
    # 2. Navigate to quote record view
    # 3. Add New Comment and verify that comment is added sucessfully
    # 4. Edit Comment record but don't commit
    # 5. Create a new QLI record
    # 6. Commit changes to the first comment record
    # 7. Verify that comment is updated and changes are not lost

  @quote @SFA-5222 @ZT-241
  Scenario: Quotes > Verify that comment changes are NOT lost when adding new comment during editing
    # 1. Generate quote record
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    # 2. Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    # 3. Add New Comment and Verify
    When I choose createComment on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.CommentRecord view
      | *        | description |
      | Comment1 | Comment 1   |
    When I click on save button on Comment #Quote_3Record.QliTable.CommentRecord record
    When I close alert
    Then I verify fields on #Comment1CommentRecord
      | fieldName   | value     |
      | description | Comment 1 |
    # 4. Edit Comment record but don't commit
    When I choose editLineItem on #Comment1CommentRecord
    When I provide input for #Comment1CommentRecord view
      | description      |
      | Comment 1 EDITED |
    # 5. Add new QLI record. No need to commit
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | quantity | product_template_name | discount_price | discount_amount |
      | Test1 | 2.00     | New QLI               | 100            | 2.00            |
    # 6. Commit changes to the first comment record
    When I click on save button on Comment #Comment1CommentRecord record
    # 7. Verify that comment is updated and changes are not lost
    Then I verify fields on #Comment1CommentRecord
      | fieldName   | value            |
      | description | Comment 1 EDITED |



    # TITLE: Verify that group totals and grand total values in the quote are converted to the new currency when the user changes the value of the "Currency" field in quote record view
    #
    # STEPS:
    # 1. Generate quote record linked to the account
    # 2. Add a new custom currency
    # 3. Navigate to quote record view
    # 4. Change the currency of the quote from USD to EUR and Cancel
    # 5. Verify that amounts and currency symbols in Summation bar are reverted back to originals
    # 6. Verify that amounts and currency symbols in QLI table footer are reverted back to originals
    # 7. Change the currency of the quote from USD to EUR and Save
    # 8. Verify that amounts and currency symbols in Summation bar are updated to EUR
    # 9. Verify that amounts and currency symbols in QLI Table footer are updated to EUR

  @SFA-5245 @ZT-284 @T_33439
  Scenario: Quotes record view -> Change record curency > Cancel/Save
    # 1. Generate quote record with one group and 2 QLIs linked to the account
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  |
      | Acc_1 |
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
    When I provide input for #CurrenciesDrawer.RecordView view
      | iso4217 | conversion_rate |
      | EUR     | 0.5             |
    When I click Save button on #CurrenciesDrawer header
    When I close alert
    # 3. Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I click Edit button on #Quote_3Record header
    # 4. Change the currency of the quote and cancel
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | currency_id |
      | € (EUR)     |
    When I click Cancel button on #QuotesRecord header
    # 5. Verify that numbers in QLI Grand Total bar are not updated
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value        |
      | deal_tot  | 2.00% $16.00 |
      | new_sub   | $784.00      |
      | tax       | $0.00        |
      | shipping  | $0.00        |
      | total     | $784.00      |
    # 6. Verify that numbers in QLI table footer are not updated
    Then I verify fields on QLI total footer on #Quote_3Record view
      | fieldName | value   |
      | new_sub   | $784.00 |
      | tax       | $0.00   |
      | shipping  | $0.00   |
      | total     | $784.00 |
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    When I click Edit button on #Quote_3Record header
    # 7. Change the currency of the quote and cancel
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | currency_id |
      | € (EUR)     |
    When I click Save button on #QuotesRecord header
    When I close alert
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    # 8. Verify that numbers in QLI Grand Total bar are updated
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value       |
      | deal_tot  | 2.00% €8.00 |
      | new_sub   | €392.00     |
      | tax       | €0.00       |
      | shipping  | €0.00       |
      | total     | €392.00     |
    # 9. Verify that numbers in QLI table footer are updated
    Then I verify fields on QLI total footer on #Quote_3Record view
      | fieldName | value   |
      | new_sub   | €392.00 |
      | tax       | €0.00   |
      | shipping  | €0.00   |
      | total     | €392.00 |




    # TITLE:  Verify that user can edit value in Shipping field
    #
    # STEPS:
    # 1. Generate quote record linked to the account
    # 2. Navigate to Quotes record view
    # 3. Edit shipping field and cancel
    # 4. Verify that shipping field is not updated
    # 5. Edit shipping field and save
    # 6. Verify that shipping field is updated

  @quote @edit_shippping
  Scenario: Quotes > Record View > Change shipping field > Cancel/Save
    # 1. Generate quote record linked to the account
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    # 2. Navigate to Quotes record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    # 3. Edit shipping field and cancel.
#    When I click Edit button on #Quote_3Record header
#    When I provide input for #Quote_3Record.QliTable view
#      | shipping |
#      | 2111     |
#    When I click Cancel button on #Quote_3Record header
#    # 4. Verify that shipping field is not updated
#    Then I verify fields on #Quote_3Record.QliTable
#      | fieldName | value |
#      | shipping  | $0.00 |
#    Then I verify fields on QLI total header on #Quote_3Record view
#      | fieldName | value |
#      | shipping  | $0.00 |
    # 5. Edit shipping field and save.
    When I click Edit button on #Quote_3Record header
    When I provide input for #Quote_3Record.QliTable view
      | shipping |
      | 1222     |
    When I click Save button on #Quote_3Record header
    When I close alert
    # 6. Verify that shipping field is updated
    Then I verify fields on #Quote_3Record.QliTable
      | fieldName | value     |
      | shipping  | $1,222.00 |
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value     |
      | shipping  | $1,222.00 |



    # TITLE:  Verify that Group Selected and Delete Selected actions function properly
    #
    # STEPS:
    # 1. Generate quote record linked to the account
    # 2. Navigate to Quotes record view
    # 3. Verify that Group Selected and Delete Selected actions are disabled
    # 4. Verify numbers in Grand Total bar
    # 5. Select all items in QLi table
    # 6. Verify that Group Selected and Delete Selected actions are enabled
    # 7. Group Selected items and give a name to a new group
    # 8. Verify new group's name and group total
    # 9. Verify that numbers in Ground Total bar haven't changed
    # 10. Select all items in QLI table > Delete Selected > Cancel
    # 11. Select all items in QLI table > Delete Selected > Confirm
    # 12. Verify that vertical ellipsis button to expand mas-update menu is disabled
    # 13. Verify numbers in Ground Total bar are all zeros
    # 14. Add new QLI records
    # 15. Verify that Group Selected and Delete Selected actions are disabled (no item selected)
    # 16. Toggle created QLI record
    # 17. Verify that Group Selected and Delete Selected actions are enabled (at least one item selected)
    # 18. Create and Toggle comment record
    # 19. Deselect both selected items
    # 20. Verify that Group Selected and Delete Selected actions are disabled (no item selected)

  @quote @Group_Selected @Delete_Selected
  Scenario: Quotes > Record View > Select All > Group/Delete Selected
    # 1. Generate quote record linked to the account
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
      # Create a product bundle
    Given ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name   |
      | Group_1 |
      # Add QLI
    Given Products records exist related via products link:
      | *name | discount_price | discount_amount | quantity |
      | QLI_1 | 100            | 2               | 2        |
      | QLI_2 | 200            | 2               | 3        |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    # 2. Navigate to Quotes record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view

    # 3. Verify that Group Selected and Delete Selected actions are disabled
    When I open QLI actions menu in #Quote_3Record.QliTable and check:
      | menu_item      | active |
      | GroupSelected  | false  |
      | DeleteSelected | false  |

    # 4. Verify numbers in Grand Total bar
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value        |
      | deal_tot  | 2.00% $16.00 |
      | new_sub   | $784.00      |
      | tax       | $0.00        |
      | shipping  | $0.00        |
      | total     | $784.00      |

    # 5. Select all items in QLi table
    When I toggle all items in #Quote_3Record.QliTable

    # 6. Verify that Group Selected and Delete Selected actions are enabled
    When I open QLI actions menu in #Quote_3Record.QliTable and check:
      | menu_item      | active |
      | GroupSelected  | true   |
      | DeleteSelected | true   |

    # 7. Group Selected items and give a name to a new group
    When I choose GroupSelected from #Quote_3Record.QliTable
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | *        | name          |
      | MyGroup1 | Alex Nisevich |
    When I click on save button on Group #MyGroup1GroupRecord record
    When I close alert

    # 8. Verify new group's name and group total
    Then I verify fields on #MyGroup1GroupRecord
      | fieldName | value         |
      | new_sub   | $784.00       |
      | name      | Alex Nisevich |

    # 9. Verify that numbers in Ground Total bar haven't changed
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value        |
      | deal_tot  | 2.00% $16.00 |
      | new_sub   | $784.00      |
      | tax       | $0.00        |
      | shipping  | $0.00        |
      | total     | $784.00      |

    # 10. Select all items in QLI table > Delete Selected > Cancel
    When I toggle all items in #Quote_3Record.QliTable
    When I choose DeleteSelected from #Quote_3Record.QliTable
    When I Cancel confirmation alert

    # 11. Select all items > Delete > Confirm
    When I choose DeleteSelected from #Quote_3Record.QliTable
    When I Confirm confirmation alert
    When I close alert

    # 12. Verify that vertical ellipsis button to expand mas-update menu is disabled after all items are deleted  from QLi table
    When I open QLI actions menu in #Quote_3Record.QliTable and check:
      | menu_item      | active |
      | massUpdateMenu | false  |

    # 13. Verify numbers in Ground Total bar are all zeros after all items are deleted from QLI table
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value       |
      | deal_tot  | 0.00% $0.00 |
      | new_sub   | $0.00       |
      | tax       | $0.00       |
      | shipping  | $0.00       |
      | total     | $0.00       |

    # 14. Add brand new QLI records
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | quantity | product_template_name | discount_price | discount_amount |
      | Test1 | 2.00     | New QLI               | 100            | 2.00            |
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert

    # 15. Verify that Group Selected and Delete Selected actions are disabled (no item selected)
    When I open QLI actions menu in #Quote_3Record.QliTable and check:
      | menu_item      | active |
      | massUpdateMenu | true   |
      | GroupSelected  | false  |
      | DeleteSelected | false  |

    # 16. Toggle created QLI record
    When I toggle #Test1QLIRecord

    # 17. Verify that Group Selected and Delete Selected actions are enabled (at least one item selected)
    When I open QLI actions menu in #Quote_3Record.QliTable and check:
      | menu_item      | active |
      | massUpdateMenu | true   |
      | GroupSelected  | true   |
      | DeleteSelected | true   |

    # 18. Create and Toggle comment record
    When I choose createComment on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.CommentRecord view
      | *     | description              |
      | Test2 | Comment added by seedbed |
    When I click on save button on QLI #Quote_3Record.QliTable.CommentRecord record
    When I close alert
    When I toggle #Test2CommentRecord

    # 19. Deselect both selected items
    When I toggle #Test1QLIRecord
    When I toggle #Test2CommentRecord

    # 20. Verify that Group Selected and Delete Selected actions are disabled (no item selected)
    When I open QLI actions menu in #Quote_3Record.QliTable and check:
      | menu_item      | active |
      | GroupSelected  | false  |
      | DeleteSelected | false  |


    # TITLE:  Verify that Group Totals are updated properly after 'Group Selected' mass update action is triggered
    #
    # STEPS:
    # 1. Generate quote record with one group and 2 QLIs linked to the account
    # 2. Navigate to Quotes record view
    # 3. Add a new group 'Group 1'
    # 4. Add a new group 'Group 2'
    # 5. Add a new comment to group 'Group 1'
    # 6. Add a new comment to group 'Group 2'
    # 7. Add a new QLI to group 'Group 1'
    # 8. Add a new QLI to group 'Group 2'
    # 9. Toggle two QLI items: one from 'Group 0' and one from 'Group 1'
    # 10. Group Selected items to generate a new group and give a name 'Group 3' to a new group
    # 11. Verify old group "Group 1" sub-total
    # 12. Verify newly generated group 'Group 3' sub-total
    # 13. Toggle two QLI items: one from 'Group 0' and one from 'Group 2'
    # 14. Group Selected items to generate a new group and give a name 'Group 4'to a new group
    # 15. Verify old group "Group 2" sub-total
    # 16. Verify newly generated group 'Group 4' sub-total
    # 17. Add Taxes and Shipping to the quote record and Save
    # 18. Verify that all amounts in QLI Grand Total bar are calculated properly
    # 19. Delete group 'Group 3' and confirm
    # 20. Delete group 'Group 4' and confirm
    # 21. Verify that all amounts in QLI Grand Total bar are the same after groups 'Group 3' and 'Group 4' are deleted
    #     (Note: Items are moved to groupless section of QLI table when a group is deleted but Grand Totals stay the same)
    # 22. Toggle all items in QLI table
    # 23. Choose 'Delete Selected' mass update action in QLI table
    # 24. Verify that vertical ellipsis button to expand mas-update menu is disabled after all items are deleted from QLI table
    # 25. Verify numbers in Ground Total bar are updated after all items are deleted from QLI table

  @add_items_to_specified_dgroup @grpoup_selected_items @pr
  Scenario: Quotes > Add QLI/Comment to Specific Group > Group Selected
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
      | Group 0 |
      # Add QLIs
    Given Products records exist related via products link:
      | *name | discount_price | discount_amount | quantity |
      | QLI_3 | 100            | 2               | 2        |
      | QLI_4 | 200            | 2               | 3        |
    Given TaxRates records exist:
      | *name | list_order | status | value |
      | Tax_1 | 4          | Active | 10.00 |
    Given I open about view and login
    # 2. Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view

    # 3. Add a new group 'Group 1'
    When I choose createGroup on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | *        | name    |
      | MyGroup1 | Group 1 |
    When I click on save button on Group #MyGroup1GroupRecord record
    When I close alert

    # 4. Add a new group 'Group 2'
    When I choose createGroup on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | *        | name    |
      | MyGroup2 | Group 2 |
    When I click on save button on Group #MyGroup2GroupRecord record
    When I close alert

    # 5. Add a new comment to group 'Group 1'
    When I choose to addComment to #MyGroup1GroupRecord
    When I provide input for #Quote_3Record.QliTable.CommentRecord view
      | *        | description |
      | Comment1 | Comment 1   |
    When I click on save button on Comment #Quote_3Record.QliTable.CommentRecord record
    When I close alert

    # 6. Add a new comment to group 'Group 2'
    When I choose to addComment to #MyGroup2GroupRecord
    When I provide input for #Quote_3Record.QliTable.CommentRecord view
      | *        | description |
      | Comment2 | Comment 2   |
    When I click on save button on Comment #Quote_3Record.QliTable.CommentRecord record
    When I close alert

    # 7. Add a new QLI to group 'Group 1'
    When I choose to addLineItem to #MyGroup1GroupRecord
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | quantity | product_template_name | discount_price | discount_amount |
      | QLI_1 | 3.5      | Prod_1                | 175            | 4.75            |
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert
    Then I verify fields on #MyGroup1GroupRecord
      | fieldName | value   |
      | new_sub   | $583.41 |

    # 8. Add a new QLI to group 'Group 2'
    When I choose to addLineItem to #MyGroup2GroupRecord
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | quantity | product_template_name | discount_price | discount_amount |
      | QLI_2 | 2.00     | Prod_2                | 100            | 2.00            |
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert
    Then I verify fields on #MyGroup2GroupRecord
      | fieldName | value   |
      | new_sub   | $196.00 |

    # 9. Toggle two QLI items: one groupless and one from 'Group 1'
    When I toggle #QLI_1QLIRecord
    When I toggle #QLI_3QLIRecord

    # 10. Group Selected items to generate a new group and give a name to a new group
    When I choose GroupSelected from #Quote_3Record.QliTable
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | *        | name    |
      | MyGroup3 | Group 3 |
    When I click on save button on Group #MyGroup3GroupRecord record
    When I close alert

    # 11. Verify old group "Group 1" sub-total
    Then I verify fields on #MyGroup1GroupRecord
      | fieldName | value |
      | new_sub   | $0.00 |

    # 12. Verify newly generated 'Group 3' sub-total
    Then I verify fields on #MyGroup3GroupRecord
      | fieldName | value   |
      | new_sub   | $779.41 |

    # 13. Toggle two QLI items: one from 'Group 0' and one from 'Group 2'
    When I toggle #QLI_2QLIRecord
    When I toggle #QLI_4QLIRecord

    # 14. Group Selected items to generate a new group and give a name to a new group
    When I choose GroupSelected from #Quote_3Record.QliTable
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | *        | name    |
      | MyGroup4 | Group 4 |
    When I click on save button on Group #MyGroup4GroupRecord record
    When I close alert

    # 15. Verify old group "Group 2" sub-total
    Then I verify fields on #MyGroup2GroupRecord
      | fieldName | value |
      | new_sub   | $0.00 |

    # 16. Verify newly generated group 'Group 4' sub-total
    Then I verify fields on #MyGroup4GroupRecord
      | fieldName | value   |
      | new_sub   | $784.00 |

    # 17. Add Taxes and Shipping to the quote record and Save
    When I click Edit button on #Quote_3Record header
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | taxrate_name |
      | Tax_1        |
    When I provide input for #Quote_3Record.QliTable view
      | shipping |
      | 280.25   |
    When I click Save button on #QuotesRecord header
    When I close alert

    # 18. Verify that all amounts in QLI Grand Total bar are calculated properly
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value        |
      | deal_tot  | 3.04% $49.09 |
      | new_sub   | $1,563.41    |
      | tax       | $156.34      |
      | shipping  | $280.25      |
      | total     | $2,000.00    |

    # 19. Delete group 'Group 3' and confirm
    When I choose deleteGroup on #MyGroup3GroupRecord
    When I Confirm confirmation alert

    # 20. Delete group 'Group 4' and confirm
    When I choose deleteGroup on #MyGroup4GroupRecord
    When I Confirm confirmation alert

    # 21. Verify that all amounts in QLI Grand Total bar are the same after groups are deleted
    #  (Note: Items are moved to groupless section of QLI table when a group is deleted but Grand Totals stay the same)
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value        |
      | deal_tot  | 3.04% $49.09 |
      | new_sub   | $1,563.41    |
      | tax       | $156.34      |
      | shipping  | $280.25      |
      | total     | $2,000.00    |

    # 22. Toggle all items in QLI table
    When I toggle all items in #Quote_3Record.QliTable

    # 23. Choose 'Delete Selected' mass update action in QLI table
    When I choose DeleteSelected from #Quote_3Record.QliTable
    When I Confirm confirmation alert
    When I close alert

    # 24. Verify that vertical ellipsis button to expand mas-update menu is disabled after all items are deleted from QLI table
    When I open QLI actions menu in #Quote_3Record.QliTable and check:
      | menu_item      | active |
      | massUpdateMenu | false  |

    # 25. Verify numbers in Ground Total bar are updated after all items are deleted from QLI table
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value       |
      | deal_tot  | 0.00% $0.00 |
      | new_sub   | $0.00       |
      | tax       | $0.00       |
      | shipping  | $280.25     |
      | total     | $280.25     |

  @email_quote_invoice
  Scenario: Quotes > Record View > Email > Invoice
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed | quote_stage |
      | Quote_3 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link:
      | name  | email                      |
      | Acc_1 | test@example.com (primary) |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I open actions menu in #Quote_3Record
    When I click EmailQuote button on #Quote_3Record header
    When I close alert
    Then I should see #EmailsRecord view
    Then I verify fields on #EmailsRecord.HeaderView
      | fieldName | value   |
      | name      | Quote_3 |
    Then I verify fields on #EmailsRecord.RecordView
      | fieldName              | value                                  |
      | attachments_collection | Email Attachment : Quote_3_Invoice.pdf |
      | from_collection        | Administrator                          |
      | to_collection          | Acc_1                                  |



    # TITLE:  Verify that Group Totals are updated properly after editing one of the grouped QLIs
    #
    # STEPS:
    # Generate quote record with 2 groupless QLIs
    # Add EUR currency to system
    # Navigate to quote record view
    # Select all items in QLI table
    # Group Selected items and give a name to a new group
    # Verify group name and total
    # Edit one of the QLIs and change QLI currency
    # Verify Group Total before QLI changes are saved
    # Save QLI changes
    # Verify Group Total
    # Verify Grand Total in QLI table header bar

  @SFA-5555
  Scenario: Quotes > Verify that group subtotal is correct after editing QLI record
    # Generate quote record with 2 groupless QLIs
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2020-10-19T19:20:22+00:00  | Negotiation |
    And ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name   | default_group |
      | Group 0 | true          |
    And Products records exist related via products link:
      | *name | discount_price | discount_amount | quantity |
      | QLI_1 | 100            | 2               | 2        |
      | QLI_2 | 200            | 2               | 3        |

    Given I open about view and login
    # Add EUR currency
    When I add new currency
      | iso4217 | conversion_rate |
      | EUR     | 0.5             |

    # Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView

    # Select all items in QLI table
    When I toggle all items in #Quote_3Record.QliTable

    # Group Selected items and give a name to a new group
    When I choose GroupSelected from #Quote_3Record.QliTable
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | *        | name          |
      | MyGroup1 | Alex Nisevich |
    When I click on save button on Group #MyGroup1GroupRecord record
    When I close alert

    # Verify group name and total
    Then I verify fields on #MyGroup1GroupRecord
      | fieldName | value         |
      | new_sub   | $784.00       |
      | name      | Alex Nisevich |

    # Edit one of the QLIs and change QLI currency
    When I choose editLineItem on #QLI_1QLIRecord
    When I provide input for #QLI_1QLIRecord view
      | quantity | currency_id | discount_price | mft_part_num |
      | 4        | € (EUR)     | 150.00         | abc123       |

    # Verify Group Total before QLI changes are saved
    Then I verify fields on #MyGroup1GroupRecord
      | fieldName | value     |
      | new_sub   | $1,764.00 |

    # Save QLI changes
    When I click on save button on QLI #QLI_1QLIRecord record

    # Verify Group Total
    Then I verify fields on #MyGroup1GroupRecord
      | fieldName | value     |
      | new_sub   | $1,764.00 |

    # Verify Grand Total in QLI table header bar
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value        |
      | deal_tot  | 2.00% $36.00 |
      | new_sub   | $1,764.00    |
      | total     | $1,764.00    |


  @quote_discount_amount @job3
  Scenario: Quotes > Verify that discount amount is recalculated properly when currency of the quote record or QLI record is changed
    # Generate quote record
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_1 | 2020-10-19T19:20:22+00:00  | Negotiation |
    # Generate account and link it to the quote
    And Accounts records exist related via billing_accounts link to *Quote_1:
      | name  | billing_address_street | billing_address_city | billing_address_state | billing_address_postalcode | billing_address_country |
      | Acc_1 | 10050 N Wolfe Rd       | Cupertino            | CA                    | 95014                      | USA                     |
    # Generate 2 products in the Product Catalog
    And ProductTemplates records exist:
      | *      | name             | discount_price | cost_price | list_price | quantity |
      | Prod_1 | 100 USD - 90 EUR | 100            | 100        | 100        | 1        |
      | Prod_2 | 100 USD - 50 JPY | 100            | 100        | 100        | 1        |

    Given I open about view and login
    # Add EUR currency
    When I add new currency
      | iso4217 | conversion_rate |
      | EUR     | 0.9             |
    # Add JPY currency
    And I add new currency
      | iso4217 | conversion_rate |
      | JPY     | 0.5             |

    # Update currency of the first product in Product Catalog
    When I update ProductTemplates *Prod_1 with the following values:
      | currency_id |
      | € (EUR)     |

    # Update currency of the second product in Product Catalog
    And I update ProductTemplates *Prod_2 with the following values:
      | currency_id |
      | ¥ (JPY)     |

    # Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_1 in #QuotesList.ListView

    # Add First QLI from Prod_1
    When I choose createLineItem on QLI section on #Quote_1Record view
    When I provide input for #Quote_1Record.QliTable.QliRecord view
      | *     | product_template_name | discount_select | discount_amount |
      | QLI_1 | 100 USD - 90 EUR      | € Euro          | 10.00           |
    When I click on save button on QLI #Quote_1Record.QliTable.QliRecord record
    When I close alert

    # Add Second QLI from Prod_2
    When I choose createLineItem on QLI section on #Quote_1Record view
    When I provide input for #Quote_1Record.QliTable.QliRecord view
      | *     | product_template_name | discount_select | discount_amount |
      | QLI_2 | 100 USD - 50 JPY      | ¥ Yen           | 10.00           |
    When I click on save button on QLI #Quote_1Record.QliTable.QliRecord record
    When I close alert

    # Verify Grand Total in QLI table header bar
    Then I verify fields on QLI total header on #Quote_1Record view
      | fieldName | value         |
      | deal_tot  | 15.56% $31.11 |
      | new_sub   | $168.89       |
      | total     | $168.89       |

    # Change currency of the quote record to EUR
    When I click Edit button on #Quote_1Record header
    When I toggle Quote_Settings panel on #Quote_1Record.RecordView view
    When I provide input for #Quote_1Record.RecordView view
      | currency_id |
      | € (EUR)     |

    # Verify First QLI amounts
    Then I verify fields on #QLI_1QLIRecord
      | fieldName       | value  |
      | discount_price  | €90.00 |
      | discount_amount | €10.00 |
      | total_amount    | €80.00 |

    # Verify Second QLI amounts
    Then I verify fields on #QLI_2QLIRecord
      | fieldName       | value        |
      | discount_price  | ¥50.00€90.00 |
      | discount_amount | ¥10.00€18.00 |
      | total_amount    | ¥40.00€72.00 |

    # Verify Grand Total in QLI table header bar
    Then I verify fields on QLI total header on #Quote_1Record view
      | fieldName | value         |
      | deal_tot  | 15.56% €28.00 |
      | new_sub   | €152.00       |
      | total     | €152.00       |

    # Change currency of the quote record again to JPY
    When I provide input for #Quote_1Record.RecordView view
      | currency_id |
      | ¥ (JPY)     |

    # Verify First QLI amounts
    Then I verify fields on #QLI_1QLIRecord
      | fieldName       | value        |
      | discount_price  | €90.00¥50.00 |
      | discount_amount | €10.00¥5.56  |
      | total_amount    | €80.00¥44.44 |

    # Verify Second QLI amounts
    Then I verify fields on #QLI_2QLIRecord
      | fieldName       | value  |
      | discount_price  | ¥50.00 |
      | discount_amount | ¥10.00 |
      | total_amount    | ¥40.00 |

    # Verify Grand Total in QLI table header bar
    Then I verify fields on QLI total header on #Quote_1Record view
      | fieldName | value         |
      | deal_tot  | 15.56% ¥15.56 |
      | new_sub   | ¥84.44        |
      | total     | ¥84.44        |

    # Save quote
    When I click Save button on #QuotesRecord header
    When I close alert

    # Change discount type (from amount to percentage) and update currency of the QLI_1
    When I choose editLineItem on #QLI_1QLIRecord
    When I provide input for #QLI_1QLIRecord view
      | discount_select | currency_id |
      | % Percent       | $ (USD)     |

    # Save QLI changes
    When I click on save button on QLI #QLI_1QLIRecord record
    When I close alert

    # Verify First QLI amounts
    Then I verify fields on #QLI_1QLIRecord
      | fieldName       | value         |
      | discount_price  | $100.00¥50.00 |
      | discount_amount | 10.00%        |
      | total_amount    | $90.00¥45.00  |

    # Verify Grand Total in QLI table header bar
    Then I verify fields on QLI total header on #Quote_1Record view
      | fieldName | value         |
      | deal_tot  | 15.00% ¥15.00 |
      | new_sub   | ¥85.00        |
      | total     | ¥85.00        |

    # Change currency of the QLI_1
    When I choose editLineItem on #QLI_1QLIRecord
    When I provide input for #QLI_1QLIRecord view
      | currency_id |
      | € (EUR)     |

    # Save QLI changes
    When I click on save button on QLI #QLI_1QLIRecord record
    When I close alert

    # Verify that percentage of the discount is not converted when currency is changed
    Then I verify fields on #QLI_1QLIRecord
      | fieldName       | value        |
      | discount_price  | €90.00¥50.00 |
      | discount_amount | 10.00%       |
      | total_amount    | €81.00¥45.00 |

  @quote_delete_group @quote_currency @job5
  Scenario: Quotes > Verify there is no JS error when quote currency is changed after group is deleted
    # Generate quote record
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_1 | 2020-10-19T19:20:22+00:00  | Negotiation |
    # Generate account and link it to the quote
    And Accounts records exist related via billing_accounts link to *Quote_1:
      | name  | billing_address_street | billing_address_city | billing_address_state | billing_address_postalcode | billing_address_country |
      | Acc_1 | 10050 N Wolfe Rd       | Cupertino            | CA                    | 95014                      | USA                     |
    # Create a product bundle
    And ProductBundles records exist related via product_bundles link to *Quote_1:
      | *name |
      | Gr_1  |
    # Add QLI
    And Products records exist related via products link:
      | *name | discount_price | discount_amount | quantity |
      | QLI_1 | 100            | 2               | 2        |
      | QLI_2 | 200            | 2               | 3        |

    Given I open about view and login
    # Add EUR currency
    When I add new currency
      | iso4217 | conversion_rate |
      | EUR     | 0.9             |

    # Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_1 in #QuotesList.ListView

    # Verify Grand Total in QLI table header bar BEFORE deleting the group
    Then I verify fields on QLI total header on #Quote_1Record view
      | fieldName | value        |
      | deal_tot  | 2.00% $16.00 |
      | new_sub   | $784.00      |
      | total     | $784.00      |

    # Delete group
    When I choose deleteGroup on #Gr_1GroupRecord
    When I Confirm confirmation alert
    When I close alert

    # Verify Grand Total in QLI table header bar AFTER deleting the group
    Then I verify fields on QLI total header on #Quote_1Record view
      | fieldName | value        |
      | deal_tot  | 2.00% $16.00 |
      | new_sub   | $784.00      |
      | total     | $784.00      |

    # Change quote currency to EUR
    When I click Edit button on #Quote_1Record header
    When I toggle Quote_Settings panel on #Quote_1Record.RecordView view
    When I provide input for #Quote_1Record.RecordView view
      | currency_id |
      | € (EUR)     |
    When I click Save button on #Quote_1Record header
    When I close alert

    # Verify Grand Total in QLI table header bar AFTER currency is changed
    Then I verify fields on QLI total header on #Quote_1Record view
      | fieldName | value        |
      | deal_tot  | 2.00% €14.40 |
      | new_sub   | €705.60      |
      | total     | €705.60      |

    # Toggle two QLI items: one from 'Group 0' and one from 'Group 2'
    When I toggle #QLI_1QLIRecord
    When I toggle #QLI_2QLIRecord

    # Group Selected items to generate a new group and give a name to a new group
    When I choose GroupSelected from #Quote_1Record.QliTable
    When I provide input for #Quote_1Record.QliTable.GroupRecord view
      | *        | name      |
      | NewGroup | New Group |
    When I click on save button on Group #NewGroupGroupRecord record
    When I close alert

    # Change quote currency to USD
    When I click Edit button on #Quote_1Record header
    When I provide input for #Quote_1Record.RecordView view
      | currency_id |
      | $ (USD)     |
    When I click Save button on #Quote_1Record header
    When I close alert

    # Verify new group's name and group total
    Then I verify fields on #NewGroupGroupRecord
      | fieldName | value     |
      | new_sub   | $784.00   |
      | name      | New Group |

    # Verify Grand Total in QLI table header bar AFTER creating the group
    Then I verify fields on QLI total header on #Quote_1Record view
      | fieldName | value        |
      | deal_tot  | 2.00% $16.00 |
      | new_sub   | $784.00      |
      | total     | $784.00      |
