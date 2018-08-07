# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@copy_quotes @pr
Feature: Copy Quote E2E testing

  Background:
    Given I use default account
    Given I launch App

    # TITLE: Copy Quote
    #
    # STEPS:
    # 1. Generate quote record with one group and 2 QLIs linked to the account, contact and etc.
    # 2. Add new EUR currency to Sugar instance
    # 3. Navigate to Product Template record view and change record's currency to EUR (needed for ZT-15)
    # 4. Navigate to quote record view
    # 5. Apply tax rate and shipping to quote before copy
    # 6. Verify that Copy menu item is present and active in Actions dropdown of quote record (ZT-7)
    # 7. Verify that copy is not allowed if at least one QLI item is in create mode (ZT-8 ZT-12 ZT-13)
    # 8. Check the alert message properties
    # 9. Verify that copy is not allowed if at least one Comment item is in create mode (ZT-13)
    # 10. Verify that copy is not allowed if at least one QLI or Comment is in create mode (ZT-13)
    # 11. Create a copy of the quote record and Cancel (ZT-9)
    # 12. Add new group before copy quote to verify that it is OK to copy quote when group is in the edit mode
    # 13. Add new QLI from product template which is created in currency (EUR) different from default (USD) currency (ZT-15)
    # 14. Create a copy of the quote record and Save (ZT-10 ZT-12)
    # 15. Verify data in quote record view is copied correctly
    # 16. Verify that data in QLI table Grant Total bar is successfully copied over
    # 17. Verify that data in QLI table footer is successfully copied over


  @quote_copy @ZT-7 @ZT-8 @ZT-9 @ZT-10 @ZT-12 @ZT-13 @ZT-14 @ZT-15
  Scenario: Quotes > Copy Quote
    # 1. Generate quote record with one group and 2 QLIs linked to the account
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage | purchase_order_num | payment_terms | description | original_po_date          | date_quote_closed         | date_order_shipped        | order_stage |
      | Quote_3 | 2020-10-19T19:20:22+00:00  | Negotiation | 1234ABC            | Net 30        | Fresh Copy  | 2020-10-19T19:20:22+00:00 | 2020-10-20T19:20:22+00:00 | 2020-10-21T19:20:22+00:00 | Confirmed   |

      # Create billing account record linked to the quote
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here 1  | 220051                     | WA                    | USA                     |

      # Create shipping account record linked to the quote
    Given Accounts records exist:
      | name  | shipping_address_city | shipping_address_street | shipping_address_postalcode | shipping_address_state | shipping_address_country |
      | Acc_2 | City 2                | Street address here 2   | 220052                      | CA                     | USA                      |

      # Create billing contact record linked to the quote
    Given Contacts records exist related via billing_contacts link to *Quote_3:
      | first_name | last_name | title       |
      | Alex       | Nisevich  | Software QA |

      # Create shipping contact record linked to the quote
    Given Contacts records exist related via shipping_contacts link to *Quote_3:
      | first_name | last_name | title            |
      | Ruslan     | Golovach  | Mobile Developer |

      # Create a product bundle (aka. group)
    Given ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name   |
      | Group_1 |

      # Add Quoted Line Items records to the product bundle (aka. group)
    Given Products records exist related via products link:
      | *name | discount_price | discount_amount | quantity |
      | QLI_1 | 100            | 2               | 2        |
      | QLI_2 | 200            | 2               | 3        |

      # Generate Tax Rate record
    Given TaxRates records exist:
      | *name | list_order | status | value |
      | Tax_1 | 4          | Active | 10.00 |

      # Generate Non-taxable product "Prod_1" in Product Catalog
    Given ProductTemplates records exist:
      | *name  | discount_price | cost_price | list_price | quantity | mft_part_num                 | tax_class   |
      | Prod_1 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | Non-Taxable |

      # Generate Shipping Provider record
    Given Shippers records exist related via shippers link to *Quote_3:
      | *name | list_order | status |
      | UPS   | 1          | Active |

    Given I open about view and login
    # 2. Add new EUR currency to Sugar instance
    When I go to "Currencies" url
    When I click Create button on #CurrenciesList header
    When I provide input for #CurrenciesDrawer.RecordView view
      | iso4217 | conversion_rate |
      | EUR     | 0.5             |
    When I click Save button on #CurrenciesDrawer header
    When I close alert

    # 3. Navigate to Product Template record view and change record's currency to EUR
    When I go to "ProductTemplates" url
    When I select *Prod_1 in #ProductTemplatesList.ListView
    Then I should see #Prod_1Record view
    When I click Edit button on #Prod_1Record header
    When I provide input for #Prod_1Record.RecordView view
      | currency_id |
      | € (EUR)     |
    When I click Save button on #Prod_1Record header
    When I close alert

    # 4. Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view

    # 5. Apply tax rate and shipping to quote before copy
    When I click Edit button on #Quote_3Record header
    When I toggle Billing_and_Shipping panel on #Quote_3Record.RecordView view
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    When I toggle Show_More panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | taxrate_name | tag   | shipper_name | show_line_nums | shipping_account_name |
      | Tax_1        | Test1 | UPS          | false          | Acc_2                 |
    When I Confirm confirmation alert
    When I provide input for #Quote_3Record.QliTable view
      | shipping |
      | 200      |
    When I click Save button on #Quote_3Record header
    When I close alert

    # 6. Verify that Copy menu item is present and active in Actions dropdown of quote record (ZT-7)
    When I open actions menu in #Quote_3Record and check
      | menu_item | active |
      | copy      | true   |

    # 7. Verify that copy is not allowed if at least one QLI item is in create mode (ZT-12 ZT-13)
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | quantity | product_template_name | discount_price | discount_amount |
      | Test1 | 2.00     | Some random name      | 100            | 2.00            |
    When I open actions menu in #Quote_3Record
      # Try to copy quote record while the QLI item is in edit mode
    When I choose Copy from actions menu in #Quote_3Record

    # 8. Check the alert message properties
    Then I check alert
      | type  | message                                                                                                             |
      | Error | Error Please save any active line items by clicking the blue check mark next to each one before copying this Quote. |

    When I close alert
    # Save new QLI record
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert

    # 9. Verify that copy is not allowed if at least one Comment item is in create mode (ZT-13)
    When I choose createComment on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.CommentRecord view
      | *        | description   |
      | Comment1 | Alex Nisevich |
    When I open actions menu in #Quote_3Record
    # Try to copy quote record while the comment item is in edit mode
    When I choose Copy from actions menu in #Quote_3Record
    When I close alert
    # Save Comment record
    When I click on save button on Comment #Quote_3Record.QliTable.CommentRecord record
    When I close alert

    # 10. Verify that copy is not allowed if at least one QLI or Comment is in edit mode (ZT-13)
      # Edit existing QLI
    When I choose editLineItem on #Test1QLIRecord
    When I provide input for #Test1QLIRecord view
      | quantity | discount_price | discount_amount | product_template_name |
      | 4        | 150.00         | 4.00            | New Name              |
      # Make sure Grand Totals are updated based on the provided info
    Then I verify fields on QLI total header on #QuotesRecord view
      | fieldName | value        |
      | deal_tot  | 2.86% $40.00 |
      | new_sub   | $1,360.00    |
      | tax       | $136.00      |
      | shipping  | $200.00      |
      | total     | $1,696.00    |
      # Try to Copy quote > Copy is not allowed
    When I open actions menu in #Quote_3Record
    When I choose Copy from actions menu in #Quote_3Record
    When I close alert
      # Edit existing comment
    When I choose editLineItem on #Comment1CommentRecord
    When I provide input for #Comment1CommentRecord view
      | description       |
      | Seedbed Copy test |
      # Try to Copy quote > Copy is not allowed
    When I open actions menu in #Quote_3Record
    When I choose Copy from actions menu in #Quote_3Record
    When I close alert
      # Save comment changes
    When I click on save button on Comment #Comment1CommentRecord record
    When I close alert
      # Try to Copy Quote > > Copy is not allowed
    When I open actions menu in #Quote_3Record
    When I choose Copy from actions menu in #Quote_3Record
    When I close alert
      # Cancel QLI changes
    When I click on cancel button on QLI #Test1QLIRecord record
      # Make sure grant total numbers are reverted back since QLI editing is canceled
    Then I verify fields on QLI total header on #QuotesRecord view
      | fieldName | value        |
      | deal_tot  | 2.00% $20.00 |
      | new_sub   | $980.00      |
      | tax       | $98.00       |
      | shipping  | $200.00      |
      | total     | $1,278.00    |

    # 11. Create a copy of the quote record and Cancel (ZT-8 ZT-9)
    When I open actions menu in #Quote_3Record
    When I choose Copy from actions menu in #Quote_3Record
    When I provide input for #QuotesRecord.HeaderView view
      | *       | name         |
      | Quote_4 | Quote_3_copy |
    When I click Cancel button on #QuotesRecord header
    When I Confirm confirmation alert
    Then I should see #Quote_3Record view

    # 12. Add new group before copy quote to verify that it is OK to copy quote when group is in the edit mode
    When I choose createGroup on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | name      |
      | New Group |
    When I close alert

    # 13. Add new QLI from product template which is created in currency (EUR) different from default (USD) currency (ZT-15)
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | quantity | product_template_name | discount_amount |
      | 2.00     | Prod_1                | 2.00            |
    When I open actions menu in #Quote_3Record
    # Save new QLI record
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert

    # 14. Create a copy of the quote record and Save (ZT-10 ZT-14)
    When I open actions menu in #Quote_3Record
    When I choose Copy from actions menu in #Quote_3Record
    When I provide input for #QuotesRecord.HeaderView view
      | name    |
      | Quote_3 |
    When I click Save button on #QuotesRecord header
    # Check the alert message properties
    Then I check alert
      | type    | message                                             |
      | Success | Success You successfully created the quote Quote_3. |
    When I close alert
    Then I should see #QuotesRecord view

    # 15. Verify data in quote record view is copied correctly
    Then I verify fields on #QuotesRecord.HeaderView
      | fieldName | value   |
      | name      | Quote_3 |

    # Verify data in Business Card section of the quote record
    Then I verify fields on #QuotesRecord.RecordView
      | fieldName                  | value       |
      | quote_stage                | Negotiation |
      | date_quote_expected_closed | 10/19/2020  |
      | purchase_order_num         | 1234ABC     |
      | payment_terms              | Net 30      |
      | tag                        | Test1       |
    When I toggle Business_Card panel on #Quote_3Record.RecordView view

      # Verify data in Billing and Shipping section of the quote record
    Then I verify fields on #QuotesRecord.RecordView
      | fieldName                   | value                 |
      | billing_account_name        | Acc_1                 |
      | billing_contact_name        | Alex Nisevich         |
      | billing_address_city        | City 1                |
      | billing_address_street      | Street address here 1 |
      | billing_address_postalcode  | 220051                |
      | billing_address_state       | WA                    |
      | billing_address_country     | USA                   |

      | shipping_account_name       | Acc_2                 |
      | shipping_contact_name       | Ruslan Golovach       |
      | shipping_address_city       | City 2                |
      | shipping_address_street     | Street address here 2 |
      | shipping_address_postalcode | 220052                |
      | shipping_address_state      | CA                    |
      | shipping_address_country    | USA                   |
    When I toggle Billing_and_Shipping panel on #Quote_3Record.RecordView view

      # Verify data in Quote Settings section of the quote record
    Then I verify fields on #QuotesRecord.RecordView
      | fieldName      | value   |
      | taxrate_name   | Tax_1   |
      | currency_id    | $ (USD) |
      | show_line_nums |         |
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view

      # Verify data in Quote Settings section of the quote record
    Then I verify fields on #QuotesRecord.RecordView
      | fieldName          | value            |
      | description        | Fresh Copy       |
      | original_po_date   | 10/19/2020       |
      | date_quote_closed  | 10/20/2020       |
      | date_order_shipped | 10/21/2020       |
      | shipper_name       | UPS              |
      | order_stage        | Confirmed        |
#     TODO: Need to find out why verification of assigned user field fails on CI
#     | assigned_user_name | Administrator    |
      | team_name          | Global (Primary) |

    When I toggle Show_More panel on #Quote_3Record.RecordView view

    # 16. Verify that data in QLI table Grant Total bar is successfully copied over
    Then I verify fields on QLI total header on #QuotesRecord view
      | fieldName | value        |
      | deal_tot  | 2.00% $24.00 |
      | new_sub   | $1,176.00    |
      | tax       | $98.00       |
      | shipping  | $200.00      |
      | total     | $1,474.00    |

    # 17. Verify that data in QLI table footer is successfully copied over
    Then I verify fields on QLI total footer on #QuotesRecord view
      | fieldName | value     |
      | new_sub   | $1,176.00 |
      | tax       | $98.00    |
      | shipping  | $200.00   |
      | total     | $1,474.00 |
