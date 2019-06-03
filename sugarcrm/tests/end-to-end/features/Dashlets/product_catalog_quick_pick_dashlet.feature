# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@product_catalog_quick_picks_daslet @job2
Feature: Product Catalog Quick Picks Dashlet

  Background:
    Given I use default account
    Given I launch App

    # TITLE: Product Catalog Quick Picks Dashlet - Opportunity record view
    #
    # STEPS:
    # Generate all required records: products, opportunity and RLI
    # Navigate to opportunity record view
    # Create new Dashboard
    # Add Product Catalog dashlet
    # Add Product Catalog Quick Picks Dashlets
    # Save new Dashboard
    # Verify that new dashboard is successfully created
    # Select 'Favorites' tab in Product Catalog Quick Picks dashlet
    # Verify number of records in the 'Favorites' tab
    # Add one product from the dashlet and cancel
    # Add another product from the dashlet and proceed to create a new RLI
    # Verify opportunity roll-up numbers are updated based on added RLI
    # Select 'Recently Used' tab
    # Verify number of records in the 'Recently Used' tab
    # Use the same product to create second RLI record
    # Verify opportunity roll-up numbers are updated based on added RLI
    # Refresh dashlet
    # Verify number of records in the 'Recently Used' tab
    # Navigate to RLI subpanel of opportunity and delete 'RLI_2' record
    # Verify number of records in the 'Recently Used' tab
    # Navigate to RLI subpanel of opportunity and delete 'RLI_2_1' record
    # Verify number of records in the 'Recently Used' tab
    # Select 'Favorites' tab
    # Remove 'Prod_1' from favorites in the Product Information page
    # Refresh dashlet
    # Verify number of records in the 'Favorites' tab
    # Navigate to Product Catalog list view
    # Mark "Prod_3" record as favorite in Product Catalog list view
    # Navigate back to Opportunity record view
    # Select 'Favorites' tab
    # Verify number of records in the 'Favorites' tab
    # Remove Product Catalog Quick Picks Dashlet

  @product_catalog_quick_picks_dashlet @opportunity_record_view
  Scenario: Product Catalog Quick Picks Dashlet in Opportunity record view
    # Generate Product records in Product Catalog
    Given ProductTemplates records exist:
      | *name  | discount_price | cost_price | list_price | quantity | mft_part_num                 | my_favorite |
      | Prod_1 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |
      | Prod_2 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |
      | Prod_3 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | false       |
    And Opportunities records exist:
      | *name |
      | Opp_1 |

    Given I open about view and login
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    When I close alert

    When I create new dashboard
      | *           | name               |
      | DashboardID | Products Dashboard |

    # Add Product Catalog dashlet to the dashboard
    When I add ProductCatalog dashlet to #Dashboard
      | label              |
      | My Product Catalog |

    # Add Product Catalog Quick Picks Dashlets to the dashboard
    When I add ProductCatalogQuickPicks dashlet to #Dashboard
      | label                              |
      | My Recently used and Fav. Products |

    # Verify that new dashboard is successfully created and saved
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value              |
      | name      | Products Dashboard |

    # Select 'Favorites' tab in Product Catalog Quick Picks dashlet
    When I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Verify that correct products are listed in the 'Favorites' tab
    Then I verify product *Prod_1 exists in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify product *Prod_2 exists in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify product *Prod_3 not exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Add one product from the dashlet and cancel
    When I click *Prod_1 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet
    When I click Cancel button on #Prod_1Drawer header

    # Add another product from the dashlet and proceed with creating new RLI
    When I click *Prod_2 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet
    When I click Add2Quote button on #Prod_2Drawer header
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | *     | date_closed |
      | RLI_2 | 11/20/2018  |
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert

    # Verify opportunity roll-up numbers are updated based on added RLI
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName  | value   |
      | worst_case | $100.00 |
      | amount     | $100.00 |
      | best_case  | $100.00 |

    # Select 'Recently Used' tab
    When I select Recently Used tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Verify that correct list of product exist in 'Recently Used' tab
    Then I verify product *Prod_1 not exists in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify product *Prod_2 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Use the same product to create another RLI record
    When I click *Prod_2 on Recently Used tab in #Dashboard.ProductCatalogQuickPicksDashlet
    When I click Add2Quote button on #Prod_2Drawer header
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | *       | name       |
      | RLI_2_1 | Second RLI |
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | *       | date_closed |
      | RLI_2_1 | 11/20/2018  |
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert

    # Verify opportunity roll-up numbers are updated based on added RLI
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName  | value   |
      | worst_case | $200.00 |
      | amount     | $200.00 |
      | best_case  | $200.00 |

    # Refresh dashlet
    When I refresh #Dashboard.ProductCatalogQuickPicksDashlet dashlet

    # Verify number of records in the 'Recently Used' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 1

    # Verify that product exists in the 'Recently Used' tab
    Then I verify product *Prod_1 not exists in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify product *Prod_2 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Navigate to RLI subpanel of opportunity and delete 'RLI_2' record
    When I open the revenuelineitems subpanel on #Opp_1Record view
    When I toggle checkbox for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I select Delete action in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I Confirm confirmation alert
    When I close alert

    # Verify number of records in the 'Recently Used' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 1

    # Navigate to RLI subpanel of opportunity and delete 'RLI_2_1' record
    When I open the revenuelineitems subpanel on #Opp_1Record view
    When I toggle checkbox for *RLI_2_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I select Delete action in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I Confirm confirmation alert
    When I close alert
    When I close alert

    # Verify number of records in the 'Recently Used' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 0

    # Select 'Favorites' tab
    When I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Remove 'Prod_1' from favorites in the Product Information page
    When I click *Prod_1 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet
    When I provide input for #Prod_1Drawer.HeaderView view
      | my_favorite |
      | False       |
    When I click Cancel button on #Prod_1Drawer header

    # Refresh dashlet
    When I refresh #Dashboard.ProductCatalogQuickPicksDashlet dashlet

    # Verify that product removed from favorites is no longer exist in the 'Favorites' tab
    Then I verify product *Prod_1 not exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Navigate to Product Catalog list view
    When I go to "ProductTemplates" url

    # Mark "Prod_3" record as favorite in Product Catalog list view
    When I toggle favorite for *Prod_3 in #ProductTemplatesList.ListView

    # Navigate back to Opportunity record view
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    When I close alert

    # Select 'Favorites' tab
    When I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Verify number of records in the 'Favorites' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 2

    # Verify that product added to favorites exists in the 'Favorites' tab
    Then I verify product *Prod_3 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Remove Product Catalog Quick Picks Dashlet
    When I remove #Dashboard.ProductCatalogQuickPicksDashlet dashlet
    When I Confirm confirmation alert


    # TITLE: Product Catalog Quick Picks Dashlet - Quote Record View
    #
    #
    # STEPS:
    # Generate all required records: products, quote, account, QLIs
    # Navigate to quotes record view
    # Create new dashboard
    # Add Product Catalog dashlet to the dashboard
    # Add Product Catalog Quick Picks Dashlets to the dashboard
    # Save new dashboard
    # Verify that new dashboard is successfully created and saved
    # Select 'Favorites' tab in Product Catalog Quick Picks dashlet
    # Verify number of records in the 'Favorites' tab
    # Add one product from the dashlet and cancel
    # Add another product from the dashlet and proceed with creating a new QLI record
    # Verify quote roll-up numbers are updated based on added QLI
    # Select 'Recently Used' tab
    # Verify number of records in the 'Recently Used' tab
    # Use the same product to create second QLI record
    # Verify quote roll-up numbers are updated based on added QLI
    # Refresh dashlet
    # Verify number of records in the 'Recently Used' tab
    # Delete first added QLI and confirm
    # Refresh dashlet
    # Verify number of records in the 'Recently Used' tab
    # Delete second added QLI and confirm
    # Refresh dashlet
    # Verify number of records in the 'Recently Used' tab
    # Select 'Favorites' tab
    # Remove 'Prod_1' from favorites in the Product Information page
    # Refresh dashlet
    # Verify that product removed from favorites is no longer exist in the 'Favorites' tab
    # Navigate to Product Catalog list view
    # Mark "Prod_3" record as favorite in Product Catalog list view
    # Navigate back to Quote record view
    # Select 'Favorites' tab
    # Verify that product added to favorites exists in the 'Favorites' tab
    # Remove Product Catalog Quick Picks Dashlet

  @product_catalog_quick_picks_dashlet @quote_record_view
  Scenario: Product Catalog Quick Picks Dashlet in Quotes record view
    # Generate Product records in Product Catalog
    Given ProductTemplates records exist:
      | *name  | discount_price | cost_price | list_price | quantity | mft_part_num                 | my_favorite |
      | Prod_1 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |
      | Prod_2 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |
      | Prod_3 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | false       |
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed |
      | Quote_1 | City 1               | Street address here    | 220051                     | CA                    | USA                     | 2020-10-19T19:20:22+00:00  |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    # Create a product bundle (aka. group)
    Given ProductBundles records exist related via product_bundles link to *Quote_1:
      | *name   |
      | Group_1 |
    # Add Quoted Line Items records to the product bundle (aka. group)
    Given Products records exist related via products link to *Group_1:
      | *name   | discount_price | discount_amount | quantity |
      | MyQLI_1 | 100            | 2               | 2        |
      | MyQLI_2 | 200            | 2               | 3        |

    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_1 in #QuotesList.ListView

    When I create new dashboard
      | *           | name               |
      | DashboardID | Products Dashboard |

    # Add Product Catalog dashlet to the dashboard
    When I add ProductCatalog dashlet to #Dashboard
      | label              |
      | My Product Catalog |

    # Add Product Catalog Quick Picks Dashlets to the dashboard
    When I add ProductCatalogQuickPicks dashlet to #Dashboard
      | label                              |
      | My Recently used and Fav. Products |

    # Verify that new dashboard is successfully created and saved
    Then I verify fields on #Dashboard.HeaderView
      | fieldName | value              |
      | name      | Products Dashboard |

    # Select 'Favorites' tab in Product Catalog Quick Picks dashlet
    When I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Verify number of records in the 'Favorites' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 2

    # Add one product from the dashlet and cancel
    When I click *Prod_1 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet
    When I click Cancel button on #Prod_1Drawer header

    # Add another product from the dashlet and proceed with creating a new QLI record
    When I click *Prod_2 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet
    When I click Add2Quote button on #Prod_2Drawer header
    When I provide input for #Quote_1Record.QliTable.QliRecord view
      | *     |
      | QLI_1 |
    When I click on save button on QLI #Quote_1Record.QliTable.QliRecord record
    When I close alert

    # Verify quote roll-up numbers are updated based on added QLI
    Then I verify fields on QLI total header on #Quote_1Record view
      | fieldName | value        |
      | deal_tot  | 1.78% $16.00 |
      | new_sub   | $884.00      |
      | tax       | $0.00        |
      | shipping  | $0.00        |
      | total     | $884.00      |

    # Select 'Recently Used' tab
    When I select Recently Used tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Verify number of records in the 'Recently Used' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 1

    # Use the same product to create second QLI record
    When I click *Prod_2 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet
    When I click Add2Quote button on #Prod_2Drawer header
    When I provide input for #Quote_1Record.QliTable.QliRecord view
      | *     |
      | QLI_2 |
    When I click on save button on QLI #Quote_1Record.QliTable.QliRecord record
    When I close alert

    # Verify quote roll-up numbers are updated based on added QLI
    Then I verify fields on QLI total header on #Quote_1Record view
      | fieldName | value        |
      | deal_tot  | 1.60% $16.00 |
      | new_sub   | $984.00      |
      | tax       | $0.00        |
      | shipping  | $0.00        |
      | total     | $984.00      |

    # Refresh dashlet
    When I refresh #Dashboard.ProductCatalogQuickPicksDashlet dashlet

    # Verify number of records in the 'Recently Used' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 1

    # Delete first added QLI and confirm
    When I choose deleteLineItem on #QLI_1QLIRecord
    When I Confirm confirmation alert
    When I close alert

    # Refresh dashlet
    When I refresh #Dashboard.ProductCatalogQuickPicksDashlet dashlet

    # Verify number of records in the 'Recently Used' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 1

    # Delete second added QLI and confirm
    When I choose deleteLineItem on #QLI_2QLIRecord
    When I Confirm confirmation alert
    When I close alert

    # Refresh dashlet
    When I refresh #Dashboard.ProductCatalogQuickPicksDashlet dashlet

    # Verify number of records in the 'Recently Used' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 0

    # Select 'Favorites' tab
    When I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Remove 'Prod_1' from favorites in the Product Information page
    When I click *Prod_1 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet
    When I provide input for #Prod_1Drawer.HeaderView view
      | my_favorite |
      | False       |
    When I click Cancel button on #Prod_1Drawer header

    # Refresh dashlet
    When I refresh #Dashboard.ProductCatalogQuickPicksDashlet dashlet

    # Verify that product removed from favorites is no longer exist in the 'Favorites' tab
    Then I verify product *Prod_1 not exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Navigate to Product Catalog list view
    When I go to "ProductTemplates" url

    # Mark "Prod_3" record as favorite in Product Catalog list view
    When I toggle favorite for *Prod_3 in #ProductTemplatesList.ListView

    # Navigate back to Opportunity record view
    When I choose Quotes in modules menu
    When I select *Quote_1 in #QuotesList.ListView

    # Select 'Favorites' tab
    When I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Verify that product added to favorites exists in the 'Favorites' tab
    Then I verify product *Prod_3 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Remove Product Catalog Quick Picks Dashlet
    When I remove #Dashboard.ProductCatalogQuickPicksDashlet dashlet
    When I Confirm confirmation alert



    # TITLE: Product Catalog Quick Picks Dashlet - Opportunity Create View
    #
    #
    # STEPS:
    # Generate all required records: products $ account
    # Navigate to opportunity create view
    # Select 'Favorites' tab in Product Catalog Quick Picks dashlet
    # Verify number of records in the 'Favorites' tab
    # Add product from the dashlet and proceed to create a new RLI
    # Add another product from the dashlet and proceed to create a new RLI
    # Add another product from the dashlet and proceed to create a new RLI
    # Verify opportunity roll-up numbers are updated based on added RLI
    # Delete first RLI before the new opportunity record is saved
    # Verify opportunity roll-up numbers are updated based on added RLI
    # Select 'Recently Used' tab
    # Verify number of records in the 'Recently Used' tab
    # Save Opportunity
    # Navigate to created opportunity record view
    # Verify opportunity roll-up numbers are updated based on added RLI
    # Verify number of records in the 'Recently Used' tab
    # Verify that correct products are listed in the 'Recently Used' tab

  @product_catalog_quick_picks_dashlet @opportunity_create_view
  Scenario: Product Catalog Quick Picks Dashlet in Opportunity Create View
    # Generate Product records in Product Catalog
    Given ProductTemplates records exist:
      | *name  | discount_price | cost_price | list_price | quantity | mft_part_num                 | my_favorite |
      | Prod_1 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |
      | Prod_2 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |
      | Prod_3 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |
    # Create Account record
    Given Accounts records exist:
      | *name |
      | Acc_1 |
    Given I open about view and login

    # Navigate to opportunity create view
    When I choose Opportunities in modules menu
    When I click Create button on #OpportunitiesList header

    # Select 'Favorites' tab in Product Catalog Quick Picks dashlet
    When I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Verify number of records in the 'Favorites' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 3

    # Add product from the dashlet and proceed to create a new RLI
    When I click *Prod_1 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet
    When I click Add2Quote button on #Prod_1Drawer header
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | *name | date_closed |
      | RLI_1 | 12/12/2020  |

    # Add another product from the dashlet and proceed to create a new RLI
    When I click *Prod_2 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet
    When I click Add2Quote button on #Prod_2Drawer header
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | *name | date_closed | sales_stage    |
      | RLI_2 | 12/13/2020  | Needs Analysis |

    # Add another RLI (not through Product Catalog) and select product from the Product drop-down
    When I choose addRLI on #OpportunityDrawer.RLITable view for 2 row
    When I provide input for #OpportunityDrawer.RLITable view for 3 row
      | *name | date_closed | product_template_name |
      | RLI_3 | 12/14/2020  | Prod_3                |

    # Verify opportunity roll-up numbers are updated based on added RLI
    Then I verify fields on #OpportunityDrawer.RecordView
      | fieldName | value   |
      | amount    | $300.00 |

    # Delete first RLI before the new opportunity record is saved
    When I choose removeRLI on #OpportunityDrawer.RLITable view for 1 row

    # Verify opportunity roll-up numbers are updated based on added RLI
    Then I verify fields on #OpportunityDrawer.RecordView
      | fieldName | value   |
      | amount    | $200.00 |

    # Select 'Recently Used' tab
    When I select Recently Used tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Verify number of records in the 'Recently Used' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 0

    # Save Opportunity
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name                  |
      | Opp_1 | CreateOpportunityTest |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | account_name |
      | Opp_1 | Acc_1        |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Navigate to created opportunity record view
    When I select *Opp_1 in #OpportunitiesList.ListView

    # Verify opportunity roll-up numbers are updated based on added RLI
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName   | value      |
      | amount      | $200.00    |
      | date_closed | 12/14/2020 |

    # Verify number of records in the 'Recently Used' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 2

    # Verify that correct products are listed in the 'Recently Used' tab
    Then I verify product *Prod_1 exists in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify product *Prod_2 not exists in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify product *Prod_3 exists in #Dashboard.ProductCatalogQuickPicksDashlet



    # TITLE: Product Catalog Quick Picks Dashlet - Quote Create View
    #
    #
    # STEPS:
    # Generate all required records: multiple products $ account
    # Create new non-admin user
    # Navigate to quote create view
    # Select 'Favorites' tab in Product Catalog Quick Picks dashlet
    # Verify number of records in the 'Favorites' tab
    # Add product from the dashlet and proceed to create a new QLI
    # Remove product from favorites in the Product Info page
    # Finish adding new QLI record to QLI table
    # Add another product from the dashlet and proceed to create a new QLI
    # Remove product from favorites in Product Info page
    # Finish adding new QLI record to QLI table
    # Add another product from the dashlet and proceed to create a new QLI
    # Finish adding new QLI record to QLI table
    # Verify quote roll-up numbers are updated based on added QLI
    # Save new quote
    # Verify quote roll-up numbers are updated based on added QLI
    # Verify number of records in the 'Recently Used' tab
    # Verify that correct products are listed in the 'Recently Used' tab
    # Select 'Favorites' tab
    # Verify number of records in the 'Favorites' tab
    # Logout from Admin and Login as another user
    # Navigate to quote record view
    # Verify number of records in the 'Recently Used' tab for another user
    # Select 'Favorites' tab in Product Catalog Quick Picks dashlet
    # Verify number of records in the 'Favorites' tab for another user

  @product_catalog_quick_picks_dashlet @quote_create_view @ci-excluded
  Scenario: Product Catalog Quick Picks Dashlet in Quotes Create View
    # Generate Product records in Product Catalog
    Given ProductTemplates records exist:
      | *name  | discount_price | cost_price | list_price | quantity | mft_part_num                 | my_favorite |
      | Prod_1 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |
      | Prod_2 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |
      | Prod_3 | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |
    # Create Account record
    Given Accounts records exist:
      | *name |
      | Acc_1 |
    # Create new non-admin user
    Given I create custom user "user"
    Given I open about view and login

    # Navigate to quote create view
    When I choose Quotes in modules menu
    When I click Create button on #QuotesList header

    # Select 'Favorites' tab in Product Catalog Quick Picks dashlet
    When I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Verify number of records in the 'Favorites' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 3

    # Add product from the dashlet and proceed to create a new QLI
    When I click *Prod_1 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Remove product from favorites in the Product Info page
    When I provide input for #Prod_1Drawer.HeaderView view
      | my_favorite |
      | False       |

    # Finish adding new QLI record to QLI table
    When I click Add2Quote button on #Prod_1Drawer header
    When I provide input for #QuotesRecord.QliTable.QliRecord view
      | *     |
      | QLI_1 |

    # Add another product from the dashlet and proceed to create a new QLI
    When I click *Prod_2 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Remove product from favorites in the Product Info page
    When I provide input for #Prod_2Drawer.HeaderView view
      | my_favorite |
      | False       |

    # Finish adding new QLI record to QLI table
    When I click Add2Quote button on #Prod_2Drawer header
    When I provide input for #QuotesRecord.QliTable.QliRecord view
      | *     |
      | QLI_2 |

    # Add another product from the dashlet and proceed to create a new QLI
    When I click *Prod_3 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Finish adding new QLI record to QLI table
    When I click Add2Quote button on #Prod_3Drawer header
    When I provide input for #QuotesRecord.QliTable.QliRecord view
      | *     |
      | QLI_3 |

    # Verify quote roll-up numbers are updated based on added QLI
    Then I verify fields on QLI total header on #QuotesRecord view
      | fieldName | value       |
      | deal_tot  | 0.00% $0.00 |
      | new_sub   | $300.00     |
      | tax       | $0.00       |
      | shipping  | $0.00       |
      | total     | $300.00     |

    # Save quote
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    When I provide input for #QuotesRecord.HeaderView view
      | *   | name    |
      | Q_1 | Quote_1 |
    When I provide input for #QuotesRecord.RecordView view
      | *   | date_quote_expected_closed | billing_account_name |
      | Q_1 | 12/12/2017                 | Acc_1                |
    When I Confirm confirmation alert
    When I click Save button on #QuotesRecord header
    When I close alert

    # Verify quote roll-up numbers are updated based on added QLI
    Then I verify fields on QLI total header on #Q_1Record view
      | fieldName | value       |
      | deal_tot  | 0.00% $0.00 |
      | new_sub   | $300.00     |
      | tax       | $0.00       |
      | shipping  | $0.00       |
      | total     | $300.00     |

    # Verify number of records in the 'Recently Used' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 3

    # Verify that correct products are listed in the 'Recently Used' tab
    Then I verify product *Prod_1 exists in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify product *Prod_2 exists in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify product *Prod_3 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Select 'Favorites' tab
    When I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Verify number of records in the 'Favorites' tab
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 1

    # Logout from Admin and Login as another user
    When I go to "logout" url
    When I use account "user"
    When I open Home view and login

    # Navigate to quote record view
    When I choose Quotes in modules menu
    When I select *Q_1 in #QuotesList.ListView

    # Verify number of records in the 'Recently Used' tab for another user
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 0

    # Select 'Favorites' tab in Product Catalog Quick Picks dashlet
    When I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    # Verify number of records in the 'Favorites' tab for another user
    When I verify number of records in #Dashboard.ProductCatalogQuickPicksDashlet is 0

    # Add one QLI to a quote while logged as non-admin user
    When I choose createLineItem on QLI section on #Q_1Record view
    When I provide input for #Q_1Record.QliTable.QliRecord view
      | *       | product_template_name |
      | MyQLI_1 | Prod_2                |
    #Save new QLI
    When I click on save button on QLI #Q_1Record.QliTable.QliRecord record

    # Select 'Recently Used' tab in Product Catalog Quick Picks dashlet
    When I select Recently Used tab in #Dashboard.ProductCatalogQuickPicksDashlet

    #Verify that product used to create QLI appears in Recently Used tab
    Then I verify product *Prod_2 exists in #Dashboard.ProductCatalogQuickPicksDashlet



  @product_catalog_quick_picks_dashlet
  Scenario: Product Catalog Quick Picks Dashlet > Pagination in Favorites Tab
    # Generate Product records in Product Catalog
    Given 1 ProductTemplates records exist:
      | *name           | discount_price | cost_price | list_price | quantity | mft_part_num                 | my_favorite |
      | Prod1_{{index}} | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |

    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed |
      | Quote_1 | City 1               | Street address here    | 220051                     | CA                    | USA                     | 2020-10-19T19:20:22+00:00  |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |

    Given I open about view and login
    # Navigate to quotes record view
    When I choose Quotes in modules menu
    When I select *Quote_1 in #QuotesList.ListView
    When I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet

    #Verify that page 1 of favorites only has one product
    Then I verify product *Prod1_1 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Verify that both chevron buttons are disabled
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | leftNavArrow  | true     |
      | rightNavArrow | true     |

    # Add 8 more Product Templates - 2 pages
    Given 8 ProductTemplates records exist:
      | *name           | discount_price | cost_price | list_price | quantity | mft_part_num                 | my_favorite |
      | Prod2_{{index}} | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |

    # Refresh dashlet
    When I refresh #Dashboard.ProductCatalogQuickPicksDashlet dashlet

    # Go to page 2 & verify chevron buttons state
    When I go to page 2 in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | rightNavArrow | true     |
      | leftNavArrow  | false    |

    #Verify that page 2 of favorites only has one product
    Then I verify product *Prod2_8 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Go to page 1 & verify chevron buttons state
    When I go to page 1 in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | disable |
      | rightNavArrow | false   |
      | leftNavArrow  | true    |

    Then I verify product *Prod1_1 exists in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify product *Prod2_7 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Add 8 more Product Templates - 3 pages
    Given 8 ProductTemplates records exist:
      | *name           | discount_price | cost_price | list_price | quantity | mft_part_num                 | my_favorite |
      | Prod3_{{index}} | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |

    # Refresh dashlet
    When I refresh #Dashboard.ProductCatalogQuickPicksDashlet dashlet

    When I go to page 2 in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | rightNavArrow | false    |
      | leftNavArrow  | false    |

    Then I verify product *Prod2_8 exists in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify product *Prod3_7 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    When I go to page 3 in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | rightNavArrow | true     |
      | leftNavArrow  | false    |

    #Verify that page 3 of favorites only has one product
    Then I verify product *Prod3_8 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    When I go to page 1 in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | rightNavArrow | false    |
      | leftNavArrow  | true     |

    # Add 8 more Product Templates - 4 pages
    Given 8 ProductTemplates records exist:
      | *name           | discount_price | cost_price | list_price | quantity | mft_part_num                 | my_favorite |
      | Prod4_{{index}} | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |

    # Refresh dashlet
    When I refresh #Dashboard.ProductCatalogQuickPicksDashlet dashlet

    # Go to page 4 & verify chevron buttons state
    When I go to page 4 in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | rightNavArrow | true     |
      | leftNavArrow  | false    |

    #Verify that page 4 of favorites only has one product
    Then I verify product *Prod4_8 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Go to page 3 & verify chevron buttons state
    When I go to previous page in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | rightNavArrow | false    |
      | leftNavArrow  | false    |

    Then I verify product *Prod3_8 exists in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify product *Prod4_7 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Go to page 2 & verify chevron buttons state
    When I go to previous page in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | rightNavArrow | false    |
      | leftNavArrow  | false    |

    # Go to page 1 & verify chevron buttons state
    When I go to previous page in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | rightNavArrow | false    |
      | leftNavArrow  | true     |

    # Add 8 more Product Templates - 5 pages - ellipsis are introduced
    Given 8 ProductTemplates records exist:
      | *name           | discount_price | cost_price | list_price | quantity | mft_part_num                 | my_favorite |
      | Prod5_{{index}} | 100            | 200        | 300        | 10       | B.H. Edwards Inc 72868XYZ987 | true        |

    # Refresh dashlet
    When I refresh #Dashboard.ProductCatalogQuickPicksDashlet dashlet

    # Go to page 1 & verify chevrons and ellipsis state
    When I go to page 1 in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | rightNavArrow | false    |
      | leftNavArrow  | true     |
      | rightEllipsis | false    |

    # Go to page 3 & verify chevrons and ellipsis state
    When I go to page 3 in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | rightEllipsis | false    |
      | leftEllipsis  | false    |
      | rightNavArrow | false    |
      | leftNavArrow  | false    |

    # Go to page 5 & verify chevrons and ellipsis state
    When I go to next page in #Dashboard.ProductCatalogQuickPicksDashlet
    When I go to next page in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | leftNavArrow  | false    |
      | leftEllipsis  | false    |
      | rightNavArrow | true     |

    #Verify that page 5 of favorites only has one product
    Then I verify product *Prod5_8 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    # Remove one product from favorites
    When I click *Prod5_8 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet
    When I provide input for #Prod5_8Drawer.HeaderView view
      | my_favorite |
      | False       |
    When I click Cancel button on #Prod5_8Drawer header

    # Refresh dashlet
    When I refresh #Dashboard.ProductCatalogQuickPicksDashlet dashlet

    When I go to page 4 in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | rightNavArrow | true     |
      | leftNavArrow  | false    |

    Then I verify product *Prod4_8 exists in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify product *Prod5_7 exists in #Dashboard.ProductCatalogQuickPicksDashlet

    When I go to page 3 in #Dashboard.ProductCatalogQuickPicksDashlet
    Then I verify pagination controls in #Dashboard.ProductCatalogQuickPicksDashlet
      | buttonName    | Disabled |
      | rightNavArrow | false    |
      | leftNavArrow  | false    |
