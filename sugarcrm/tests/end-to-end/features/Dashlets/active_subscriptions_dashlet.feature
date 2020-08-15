# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@dashboard @dashlets @job8 @ent-only
Feature: Active Subscription dashlet on the record view

  Background:
    Given I am logged in

  Scenario Outline: Verify that Active Subscriptions dashlet does not function without Sugar Sell license
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |

    # Add Active Subscriptions Dashlet to Accounts module
    When I choose Accounts in modules menu
    When I select *A_1 in #AccountsList.ListView

    # Create a new dashboard
    When I create new dashboard
      | *      | name                |
      | Dash_1 | Dashboard - Account |

    # Verify dashboard is successfully created
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value               |
      | name      | Dashboard - Account |

    # Add Active Subscriptions dashlet to the dashboard
    When I add ActiveSubscriptions dashlet to #Dashboard
      | label                   |
      | Very Active Deals - Acc |

    # Verify the following message
    Then I verify '<message>' message appears in #Dashboard.ActiveSubscriptionsDashlet

    Examples:
      | message                                                                                           |
      | This dashlet requires Purchases to function. Talk to your administrator about enabling Purchases. |


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


  @SS-638 @SS-805
  Scenario Outline: Verify Active Subscriptions dashlet is available on the record View
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |
      | A_2 | Account Two |
    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | service | renewable | description            |
      | Pur_1 | Purchase 1 | true    | true      | This is great purchase |

    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    And Opportunities records exist related via opportunities link:
      | *name |
      | Opp_1 |

    Given Quotes records exist:
      | *name | date_quote_expected_closed | quote_stage |
      | Q_1   | 2020-10-19T19:20:22+00:00  | Negotiation |

    # Click Create Purchased Line Items in Mega menu
    When I choose PurchasedLineItems in modules menu and select "Create Purchased Line Item" menu item
    When I click show more button on #PurchasedLineItemsDrawer view
    # Populate Header data
    When I provide input for #PurchasedLineItemsDrawer.HeaderView view
      | *     | name       |
      | PLI_1 | Chelsea FC |
    # Populate record data
    When I provide input for #PurchasedLineItemsDrawer.RecordView view
      | *     | purchase_name | date_closed | revenue | quantity | discount_amount | service_start_date | service_duration_value | service_duration_unit | service | renewable |
      | PLI_1 | Purchase 1    | 05/05/2020  | 2000    | 3        | 100             | now -30d           | 90                     | Day(s)                | true    | true      |
    # Save
    When I click Save button on #PurchasesDrawer header
    When I close alert

    # Add Active Subscriptions Dashlet to Accounts module
    When I choose Accounts in modules menu
    When I select *A_1 in #AccountsList.ListView

    # Create a new dashboard
    When I create new dashboard
      | *      | name                |
      | Dash_1 | Dashboard - Account |

    # Verify dashboard is successfully created
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value               |
      | name      | Dashboard - Account |

    # Add Active Subscriptions dashlet to the dashboard
    When I add ActiveSubscriptions dashlet to #Dashboard
      | label                   |
      | Very Active Deals - Acc |

    # Verify data in Active Subcriptions dashlet
    Then I verify *Pur_1 record info in #Dashboard.ActiveSubscriptionsDashlet.ListView
      | fieldName | value        |
      | name      | Purchase 1   |
      | quantity  | , quantity 3 |
      | date      | in 2 months  |
      | total     | $5,900.00    |

    When I choose Opportunities in modules menu

    # TODO: Remove this block after the Warning Message issue CS-862 is fixed
    When I Confirm confirmation alert

    # Edit Opportunity record
    When I select *Opp_1 in #OpportunitiesList.ListView
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | account_name |
      | Account One  |
    When I click Save button on #Opp_1Record header
    When I close alert

    # Create a new dashboard
    When I create new dashboard
      | *      | name                    |
      | Dash_2 | Dashboard - Opportunity |

    # Verify dashboard is successfully created
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value                   |
      | name      | Dashboard - Opportunity |

    # Add Active Subscriptions dashlet to the dashboard
    When I add ActiveSubscriptions dashlet to #Dashboard
      | label                   |
      | Very Active Deals - Opp |

    # Verify data in Active Subscriptions dashlet
    Then I verify *Pur_1 record info in #Dashboard.ActiveSubscriptionsDashlet.ListView
      | fieldName | value        |
      | name      | Purchase 1   |
      | quantity  | , quantity 3 |
      | date      | in 2 months  |
      | total     | $5,900.00    |

    # Edit opportunity and select another account
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | account_name |
      | Account Two  |
    When I click Save button on #Opp_1Record header
    When I close alert

    # Refresh dashlet
    When I refresh the browser
    # TODO: Remove this block after the Warning Message issue CS-862 is fixed
    When I Confirm confirmation alert
    # Verify there is no information displayed in the dashlet
    Then I verify '<message>' message appears in #Dashboard.ActiveSubscriptionsDashlet

    # Edit Quote record - add billing and shipping accounts info
    When I choose Quotes in modules menu
    When I select *Q_1 in #QuotesList.ListView
    When I click Edit button on #Q_1Record header
    When I toggle Billing_and_Shipping panel on #Q_1Record.RecordView view
    When I provide input for #QuotesRecord.RecordView view
      | billing_account_name | shipping_account_name |
      | Account One          | Account Two           |
    When I click Save button on #QuotesRecord header
    When I close alert
    When I toggle Billing_and_Shipping panel on #Q_1Record.RecordView view

    # Create a new dashboard
    When I create new dashboard
      | *      | name               |
      | Dash_3 | Dashboard - Quotes |

    # Verify dashboard is successfully created
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value              |
      | name      | Dashboard - Quotes |

    # Add Active Subscriptions dashlet to the dashboard
    When I add ActiveSubscriptions dashlet to #Dashboard
      | label                      | linked_subscriptions_account_field |
      | Very Active Deals - Quotes | Shipping Account Name              |

    # Verify there is no information displayed in the dashlet
    Then I verify '<message>' message appears in #Dashboard.ActiveSubscriptionsDashlet

    # Edit dashlet by choosing another account
    When I edit #Dashboard.ActiveSubscriptionsDashlet dashlet
    When I provide input for #AddSugarDashletDrawer view
      | linked_subscriptions_account_field |
      | Billing Account Name               |
    When I click Save button on #AddSugarDashletDrawer header

    # Verify data in Active Subscriptions dashlet
    Then I verify *Pur_1 record info in #Dashboard.ActiveSubscriptionsDashlet.ListView
      | fieldName | value        |
      | name      | Purchase 1   |
      | quantity  | , quantity 3 |
      | date      | in 2 months  |
      | total     | $5,900.00    |

    Examples:
      | message                                                                                           |
      | No active subscriptions |


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
