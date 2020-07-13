# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@bpm @job2 @pr
Feature: Assign new process user and routing type to the process

  Background:
    Given I am logged in

  @bpm_assign_new_process_user
  Scenario: Business Process > Assign new process user and type

    # Generate quote record with one group and 1 QLI linked to the account
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage | assigned_user_id |
      | Quote_1 | 2018-10-19T19:20:22+00:00  | Negotiation | 1                |
    And Accounts records exist related via billing_accounts link to *Quote_1:
      | name  | assigned_user_id |
      | Acc_1 | 1                |
    # Create a product bundle
    And ProductBundles records exist related via product_bundles link to *Quote_1:
      | *name   |
      | Group_1 |
    # Add QLI
    And Products records exist related via products link:
      | *name | discount_price | discount_amount | quantity |
      | QLI_1 | 100            | 2               | 2        |

    # Create users sally and jim
    Given I create custom user "sally"
    And I create custom user "jim"

    # Go to Process definition -> Import Process Definition
    When I choose pmse_Project in modules menu and select "Import Process Definitions" menu item

    # Import bpm file and assign record identifier to the imported record
    When I create a new record *BP_1 by importing "Quote_Approval_When_Discount_Exceeds_20%.bpm" file on #pmse_ProjectRecord.ImportBpmView
    And I close alert

    # Enable process
    When I click on Enable button for *BP_1 in #pmse_ProjectList.ListView
    And I Confirm confirmation alert
    And I close alert

    # Navigate to quote record view
    When I choose Quotes in modules menu
    And I select *Quote_1 in #QuotesList.ListView
    Then I should see #Quote_1Record view

    # Change discount amount
    When I choose editLineItem on #QLI_1QLIRecord
    When I provide input for #QLI_1QLIRecord view
      | discount_amount |
      | 21.00           |
    When I click on save button on QLI #QLI_1QLIRecord record
    When I close alert

    # Navigate to process record view
    When I choose pmse_Inbox in modules menu
    When I filter for the pmse_Inbox record *Pr_1 named "Supervisor Approval for Discount"
    When I click on ShowProcess button for *Pr_1 in #pmse_InboxList.ListView

    # Select new process user action: Select new process user, routing type and add note
    When I select new process user action for #Pr_1Record process with the following settings:
      | user  | type       | note                           |
      | sally | Round Trip | Assign to Sally to take a look |

    # Logout from Admin and Login as Sally
    When I logout
    When I use account "sally"
    When I open about view and login
    When I choose pmse_Inbox in modules menu

    # Navigate to process record view
    When I filter for the pmse_Inbox record *Pr_1 named "Supervisor Approval for Discount"
    When I click on ShowProcess button for *Pr_1 in #pmse_InboxList.ListView

    # Select new process user action: Select new process user, routing type and add note
    When I select new process user action for #Pr_1Record process with the following settings:
      | user | type    | note                         |
      | jim  | One Way | Assign to Jim to take a look |

    # Log Sally out and log Jim in
    When I logout
    When I use account "jim"
    When I wait for 2 seconds
    When I open pmse_Inbox view and login

    # Navigate to process record view
    When I filter for the pmse_Inbox record *Pr_1 named "Supervisor Approval for Discount"
    When I click on ShowProcess button for *Pr_1 in #pmse_InboxList.ListView

    # Route Process
    When I route the Business Process request on #Pr_1Record

    # Logout from Jim
    When I logout

    # Login as admin
    Given I am logged in
    When I wait for 2 seconds
    When I choose pmse_Inbox in modules menu

    # Navigate to process record view
    When I filter for the pmse_Inbox record *Pr_1 named "Supervisor Approval for Discount"
    When I click on ShowProcess button for *Pr_1 in #pmse_InboxList.ListView

    # Approve Process
    When I approve the Business Process request on #Pr_1Record

    # Verify that status of the process under Process Management is completed
    When I choose pmse_Inbox in modules menu and select "Process Management" menu item
    And I filter for the pmse_Inbox record *Pr_1 named "Quote_Approval_When_Discount_Exceeds_20%"
    Then I verify fields for *Pr_1 in #pmse_InboxList.ListView
      | fieldName  | value     |
      | cas_status | COMPLETED |
