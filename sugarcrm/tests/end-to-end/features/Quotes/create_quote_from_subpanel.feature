# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@subpanels @create_quote
Feature: Subpanel Support

  Background:
    Given I use default account
    Given I launch App

  @create_quote_record_from_AccountRecordView_Quotes_BillTo_Subpanel
  Scenario: Account Record View > Quote BillTo Subpanel > Create/Edit Quote
    Given Accounts records exist:
      | *name     | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | City 1               | Street address here    | 22051                      | WA                    | USA                     |
      | Account_B | Cupertino            | 10050 N Wolfe Rd       | 95014                      | CA                    | USA                     |
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |

    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *Account_A in #AccountsList.ListView
    When I select *Account_A in #AccountsList.ListView

    # Create Quote record from Quotes_BillTo subpanel of account record view
    When I open the quotes subpanel on #Account_ARecord view
    # Create record from the subpanel
    When I create_new record from quotes subpanel on #Account_ARecord view
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    When I provide input for #QuotesRecord.HeaderView view
      | *      | name  |
      | Quote1 | Alex2 |
    When I provide input for #QuotesRecord.RecordView view
      | *      | date_quote_expected_closed |
      | Quote1 | 12/12/2020                 |
    Then I verify fields on #QuotesRecord.RecordView
      | fieldName                  | value               |
      | billing_account_name       | Account_A           |
      | billing_address_city       | City 1              |
      | billing_address_street     | Street address here |
      | billing_address_postalcode | 22051               |
      | billing_address_state      | WA                  |
      | billing_address_country    | USA                 |

    # Add New QLI
    When I choose createLineItem on QLI section on #QuotesRecord view
    When I provide input for #QuotesRecord.QliTable.QliRecord view
      | *        | quantity | product_template_name | discount_price | discount_amount |
      | RecordID | 2.00     | Kamalal Gadget        | 100            | 2.00            |
    When I click Save button on #QuotesRecord header
    When I close alert
    When I choose Accounts in modules menu
    When I select *Account_A in #AccountsList.ListView
    When I open the quotes subpanel on #Account_ARecord view
    Then I verify fields for *Quote1 in #Account_ARecord.SubpanelsLayout.subpanels.quotes
      | fieldName                  | value      |
      | name                       | Alex2      |
      | total_usdollar             | $196.00    |
      | date_quote_expected_closed | 12/12/2020 |

    # Inline edit record in subpanel
    When I click on Edit button for *Quote1 in #Account_ARecord.SubpanelsLayout.subpanels.quotes
    When I set values for *Quote1 in #Account_ARecord.SubpanelsLayout.subpanels.quotes
      | fieldName                  | value      |
      | name                       | Alex3      |
      | date_quote_expected_closed | 12/31/2020 |
    When I click on Save button for *Quote1 in #Account_ARecord.SubpanelsLayout.subpanels.quotes
    When I close alert
    Then I verify fields for *Quote1 in #Account_ARecord.SubpanelsLayout.subpanels.quotes
      | fieldName                  | value      |
      | name                       | Alex3      |
      | total_usdollar             | $196.00    |
      | date_quote_expected_closed | 12/31/2020 |


  @create_quote_record_from_AccountRecordView_Quotes_ShipTo_Subpanel
  Scenario: Account Record View > Quote ShipTo Subpanel > Create/Edit Quote
    Given Accounts records exist:
      | *name     | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | City 1               | Street address here    | 22051                      | WA                    | USA                     |
      | Account_B | Cupertino            | 10050 N Wolfe Rd       | 95014                      | CA                    | USA                     |
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |

    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *Account_A in #AccountsList.ListView
    When I select *Account_A in #AccountsList.ListView

    # Create Quote record from Quotes_BillTo subpanel of account record view
    When I open the quotes subpanel on #Account_ARecord view
    # Create record from the subpanel
    When I create_new record from quotes_shipto subpanel on #Account_ARecord view
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    When I provide input for #QuotesRecord.HeaderView view
      | *      | name       |
      | Quote1 | My Quote 1 |
    When I provide input for #QuotesRecord.RecordView view
      | *      | date_quote_expected_closed | billing_account_name |
      | Quote1 | 12/12/2021                 | Account_B            |
    When I Confirm confirmation alert
    Then I verify fields on #QuotesRecord.RecordView
      | fieldName             | value     |
      | shipping_account_name | Account_A |

    # Add New QLI
    When I choose createLineItem on QLI section on #QuotesRecord view
    When I provide input for #QuotesRecord.QliTable.QliRecord view
      | *        | quantity | product_template_name | discount_price | discount_amount |
      | RecordID | 3.00     | Alex Gadget           | 100            | 3.00            |
    When I click Save button on #QuotesRecord header
    When I close alert
    When I choose Accounts in modules menu
    When I select *Account_A in #AccountsList.ListView
    When I open the quotes_shipto subpanel on #Account_ARecord view
    Then I verify fields for *Quote1 in #Account_ARecord.SubpanelsLayout.subpanels.quotes_shipto
      | fieldName                  | value      |
      | name                       | My Quote 1 |
      | total_usdollar             | $291.00    |
      | date_quote_expected_closed | 12/12/2021 |

    # Inline edit record in subpanel
    When I click on Edit button for *Quote1 in #Account_ARecord.SubpanelsLayout.subpanels.quotes_shipto
    When I set values for *Quote1 in #Account_ARecord.SubpanelsLayout.subpanels.quotes_shipto
      | fieldName                  | value      |
      | name                       | Alex3      |
      | date_quote_expected_closed | 12/31/2021 |
    When I click on Save button for *Quote1 in #Account_ARecord.SubpanelsLayout.subpanels.quotes_shipto
    When I close alert
    Then I verify fields for *Quote1 in #Account_ARecord.SubpanelsLayout.subpanels.quotes_shipto
      | fieldName                  | value      |
      | name                       | Alex3      |
      | total_usdollar             | $291.00    |
      | date_quote_expected_closed | 12/31/2021 |


  @create_quote_record_from_OpportunityRecordView_Quotes_Subpanel
  Scenario: Opportunity Record View > Quotes Subpanel > Create/Edit record
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | *name |
      | Opp_1 |
    Given Accounts records exist:
      | *name            |
      | Acc_1 |
    Given I open about view and login
      #Select account record for opportunity
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Record view
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | account_name    |
      | Acc_1 |
    When I click Save button on #Opp_1Record header
    When I close alert

    # Create Quote record from Quotes_BillTo subpanel of account record view
    When I open the quotes subpanel on #Opp_1Record view
    # Create record from the subpanel
    When I create_new record from quotes subpanel on #Opp_1Record view
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    When I provide input for #QuotesRecord.HeaderView view
      | *      | name       |
      | Quote1 | My Quote 1 |
    When I provide input for #QuotesRecord.RecordView view
      | *      | date_quote_expected_closed |
      | Quote1 | 12/12/2021                 |

    # Add New QLI
    When I choose createLineItem on QLI section on #QuotesRecord view
    When I provide input for #QuotesRecord.QliTable.QliRecord view
      | *        | quantity | product_template_name | discount_price | discount_amount |
      | RecordID | 3.00     | Alex Gadget           | 100            | 3.00            |
    When I click Save button on #QuotesRecord header
    When I close alert
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    When I open the quotes subpanel on #Opp_1Record view
    Then I verify fields for *Quote1 in #Opp_1Record.SubpanelsLayout.subpanels.quotes
      | fieldName                  | value      |
      | name                       | My Quote 1 |
      | total_usdollar             | $291.00    |
      | date_quote_expected_closed | 12/12/2021 |

    # Inline edit record in subpanel
    When I click on Edit button for *Quote1 in #Opp_1Record.SubpanelsLayout.subpanels.quotes
    When I set values for *Quote1 in #Opp_1Record.SubpanelsLayout.subpanels.quotes
      | fieldName                  | value      |
      | name                       | Alex3      |
      | date_quote_expected_closed | 12/31/2021 |
    When I click on Save button for *Quote1 in #Opp_1Record.SubpanelsLayout.subpanels.quotes
    When I close alert
    Then I verify fields for *Quote1 in #Opp_1Record.SubpanelsLayout.subpanels.quotes
      | fieldName                  | value      |
      | name                       | Alex3      |
      | total_usdollar             | $291.00    |
      | date_quote_expected_closed | 12/31/2021 |


    #When I link_existing record from notes subpanel on #Account_ARecord view
