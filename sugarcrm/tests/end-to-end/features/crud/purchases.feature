# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_purchases @job7 @ent-only
Feature: Purchases module verification

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


  @list
  Scenario: Purchases > List View > Preview
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |

    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | service | renewable | description            |
      | Pur_1 | Purchase 1 | true    | true      | This is great purchase |

    Then Purchases *Pur_1 should have the following values in the preview:
      | fieldName    | value                  |
      | name         | Purchase 1             |
      | account_name | Account One            |
      | service      | true                   |
      | renewable    | true                   |
      | description  | This is great purchase |


  @list-search
  Scenario Outline: Purchases > List View > Filter > Search main input
    Given 3 Purchases records exist:
      | *             | name               | service | renewable | description            |
      | Pur_{{index}} | Purchase {{index}} | true    | true      | This is great purchase |
    # Search for specific record
    When I choose Purchases in modules menu
    And I search for "Purchase <searchIndex>" in #PurchasesList.FilterView view
    # Verification if filtering is successful
    Then I should see [*Pur_<searchIndex>] on Purchases list view
    And I should not see [*Pur_1, *Pur_3] on Purchases list view
    Examples:
      | searchIndex |
      | 2           |

  @list-edit
  Scenario Outline: Purchases > List View > Inline Edit > Cancel/Save
    Given 2 Accounts records exist:
      | *           | name              |
      | A_{{index}} | Account_{{index}} |

    Given Purchases records exist related via purchases link to *A_1:
      | *     | name       | service | renewable | description            |
      | Pur_1 | Purchase 1 | true    | true      | This is great purchase |

    # Edit (or cancel editing of) record in the list view
    When I <action> *Pur_1 record in Purchases list view with the following values:
      | fieldName    | value                  |
      | name         | Purchase <changeIndex> |
      | account_name | Account_<changeIndex>  |

    # Verify if edit (or cancel) is successful
    Then Purchases *Pur_1 should have the following values in the list view:
      | fieldName    | value                    |
      | name         | Purchase <expectedIndex> |
      | account_name | Account_<expectedIndex>  |

    Examples:
      | action            | changeIndex | expectedIndex |
      | edit              | 2           | 2             |
      | cancel editing of | 2           | 1             |


  @list-delete
  Scenario Outline: Purchases > List View > Delete > OK/Cancel
    Given Purchases records exist:
      | *     | name       | service | renewable | description            |
      | Pur_1 | Purchase 1 | true    | true      | This is great purchase |
    # Delete (or Cancel deletion of) record from list view
    When I <action> *Pur_1 record in Purchases list view
    # Verify that record is (is not) deleted
    Then I <expected> see [*Pur_1] on Purchases list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |

  @delete
  Scenario Outline: Purchases > Record View > Delete
    Given Purchases records exist:
      | *     | name       | service | renewable | description            |
      | Pur_1 | Purchase 1 | true    | true      | This is great purchase |

    # Delete (or Cancel deletion of) record in the record view
    When I <action> *Pur_1 record in Purchases record view

    # Verify that record is (is not) deleted
    When I choose Purchases in modules menu
    Then I <expected> see [*Pur_1] on Purchases list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |

  @copy
  Scenario Outline: Purchases > Record View > Copy > Cancel
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |

    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | service | renewable | description            |
      | Pur_1 | Purchase 1 | true    | true      | This is great purchase |

    And PurchasedLineItems records exist related via purchasedlineitems link to *Pur_1:
      | *     | name  | revenue | date_closed | quantity | service_start_date | service_end_date | service | renewable | discount_price | description            |
      | PLI_1 | PLI_1 | 2000    | 2020-06-01  | 3.00     | 2020-06-01         | 2021-05-31       | true    | true      | 2000           | This is great purchase |

    Then Purchases *Pur_1 should have the following values:
      | fieldName  | value      |
      | start_date | 06/01/2020 |
      | end_date   | 05/31/2021 |

    # Copy (or cancel copy of) record in the record view
    When I <action> *Pur_1 record in Purchases record view with the following header values:
      | *     | name                   |
      | Pur_2 | Purchase <changeIndex> |

    # Verify if copy is (is not) created
    Then Purchases *Pur_<expectedIndex> should have the following values:
      | fieldName    | value                    |
      | name         | Purchase <expectedIndex> |
      | account_name | Account One              |
      | service      | true                     |
      | renewable    | true                     |
      | description  | This is great purchase   |
      | start_date   | 06/01/2020               |
      | end_date     | 05/31/2021               |

    Examples:
      | action         | changeIndex | expectedIndex |
      | cancel copy of | 2           | 1             |


  @copy
  Scenario Outline: Purchases > Record View > Copy > Save
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |

    And Purchases records exist related via purchases link to *A_1:
      | *     | name       | service | renewable | description            |
      | Pur_1 | Purchase 1 | true    | true      | This is great purchase |

    And PurchasedLineItems records exist related via purchasedlineitems link to *Pur_1:
      | *     | name  | revenue | date_closed | quantity | service_start_date | service_end_date | service | renewable | discount_price | description            |
      | PLI_1 | PLI_1 | 2000    | 2020-06-01  | 3.00     | 2020-06-01         | 2021-05-31       | true    | true      | 2000           | This is great purchase |

    Then Purchases *Pur_1 should have the following values:
      | fieldName  | value      |
      | start_date | 06/01/2020 |
      | end_date   | 05/31/2021 |

    # Copy (or cancel copy of) record in the record view
    When I <action> *Pur_1 record in Purchases record view with the following header values:
      | *     | name                   |
      | Pur_2 | Purchase <changeIndex> |

    # Verify if copy is (is not) created
    Then Purchases *Pur_<expectedIndex> should have the following values:
      | fieldName    | value                    |
      | name         | Purchase <expectedIndex> |
      | account_name | Account One              |
      | service      | true                     |
      | renewable    | true                     |
      | description  | This is great purchase   |
      | start_date   |                          |
      | end_date     |                          |

    Examples:
      | action | changeIndex | expectedIndex |
      | copy   | 2           | 2             |

  @create
  Scenario: Purchases > Create
    Given Accounts records exist:
      | *   | name        |
      | A_1 | Account One |
    Given ProductTemplates records exist:
      | *      | name        | discount_price | cost_price | list_price | service | service_start_date | service_duration_value | service_duration_unit | renewable |
      | Prod_1 | Product One | 100            | 200        | 300        | true    | 2020-01-01         | 2                      | year                  | false     |
    And ProductCategories records exist:
      | *name      |
      | Category_1 |
    And ProductTypes records exist:
      | *name  |
      | Type_1 |
    # Update Product with type and category
    When I choose ProductTemplates in modules menu
    When I select *Prod_1 in #ProductTemplatesList.ListView
    When I click Edit button on #Prod_1Record header
    When I provide input for #Prod_1Record.RecordView view
      | type_name | category_name |
      | Type_1    | Category_1    |
    When I click Save button on #ProductTemplatesRecord header
    When I close alert
    # Click Create Purchase in Mega menu
    When I choose Purchases in modules menu and select "Create Purchase" menu item
    When I click show more button on #PurchasesDrawer view
    # Populate Header data
    When I provide input for #PurchasesDrawer.HeaderView view
      | *     | name            |
      | Pur_1 | My New Purchase |
    # Populate record data
    When I provide input for #PurchasesDrawer.RecordView view
      | *     | account_name | tag  | commentlog  | description                  | product_template_name |
      | Pur_1 | Account One  | Alex | New Message | You've made a great purchase | Product One           |
    # Save
    When I click Save button on #PurchasesDrawer header
    When I close alert
    # Verify that record is created successfully
    Then Purchases *Pur_1 should have the following values:
      | fieldName             | value                        |
      | name                  | My New Purchase              |
      | account_name          | Account One                  |
      | product_template_name | Product One                  |
      | service               | true                         |
      | renewable             | false                        |
      | tag                   | Alex                         |
      | description           | You've made a great purchase |
      | category_name         | Category_1                   |
      | type_name             | Type_1                       |


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
