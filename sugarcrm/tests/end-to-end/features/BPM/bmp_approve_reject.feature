@bpm @job1
Feature: Importing files related to Business Process Management

  Background:
    Given I am logged in

  @bpm_approve_reject @pr
  Scenario Outline: Business Process > Approve/Reject

    # Go to Process definition -> Import Process Definition
    When I choose pmse_Project in modules menu and select "Import Process Definitions" menu item

    # Import bpm file and assign record identifier to the imported record
    When I create a new record *BP_1 by importing "PD_for_approve_or_reject_case.bpm" file on #pmse_ProjectRecord.ImportBpmView
    When I close alert

    # Verify that Business Process module list view displays imported record
    Then I verify number of records in #pmse_ProjectList.ListView is 1

    # Enable process
    When I click on Enable button for *BP_1 in #pmse_ProjectList.ListView
    When I Confirm confirmation alert
    When I close alert

    # CASE 1: APPROVE PROCESS
    # Create new account record
    When I choose Accounts in modules menu
    When I click Create button on #AccountsList header
    When I provide input for #AccountsDrawer.HeaderView view
      | *name               |
      | Approve: NewAccount |
    When I click Save button on #AccountsDrawer header
    When I close alert

    # Navigate to process record view
    When I choose pmse_Inbox in modules menu
    When I filter for the pmse_Inbox record *Pr_1 named "Activity # 1"
    When I click on ShowProcess button for *Pr_1 in #pmse_InboxList.ListView

    # Add Notes
    When I open actions menu in #Pr_1Record
    And I choose AddNotes from actions menu in #Pr_1Record
    When I add the following note to the process in BPM pop-up window:
      | note           |
      | <approvedNote> |
    When I close BPM pop-up window

    # Approve Process
    When I approve the Business Process request on #Pr_1Record

    # Verify the process record is no longer displayed in Processes list view
    When I choose pmse_Inbox in modules menu
    Then I verify number of records in #pmse_InboxList.ListView is 0

    # Verify that status of the process under Process Management is completed
    When I choose pmse_Inbox in modules menu and select "Process Management" menu item
    When I filter for the pmse_Inbox record *Pr_1 named "PD for approve or reject case"
    Then I verify fields for *Pr_1 in #pmse_InboxList.ListView
      | fieldName  | value     |
      | cas_status | COMPLETED |

    # Verify that process notes appear properly
    When I click on ViewNotes button for *Pr_1 in #pmse_InboxList.ListView
    Then I verify the last note in BPM pop-up window
      | note                         |
      | Administrator <approvedNote> |
    # Delete Process Notes
    When I delete last note in BPM pop-up window
    When I close BPM pop-up window

    # CASE 2: REJECT PROCESS
    # Create new account record
    When I choose Accounts in modules menu
    When I click Create button on #AccountsList header
    When I provide input for #AccountsDrawer.HeaderView view
      | *name              |
      | Reject: NewAccount |
    When I click Save button on #AccountsDrawer header
    When I close alert

    # Navigate to process record view
    When I choose pmse_Inbox in modules menu
    When I filter for the pmse_Inbox record *Pr_1 named "Activity # 1"
    When I click on ShowProcess button for *Pr_1 in #pmse_InboxList.ListView

    # Add Notes
    When I open actions menu in #Pr_1Record
    And I choose AddNotes from actions menu in #Pr_1Record
    When I add the following note to the process in BPM pop-up window:
      | note           |
      | <rejectedNote> |
    When I close BPM pop-up window

    # Reject Process
    When I reject the Business Process request on #Pr_1Record

    # Verify the process record is no longer displayed in Processes list view
    When I choose pmse_Inbox in modules menu
    Then I verify number of records in #pmse_InboxList.ListView is 0

    # Verify that status of the process under Process Management is completed
    When I choose pmse_Inbox in modules menu and select "Process Management" menu item
    When I filter for the pmse_Inbox record *Pr_1 named "PD for approve or reject case"
    Then I verify fields for *Pr_1 in #pmse_InboxList.ListView
      | fieldName  | value     |
      | cas_status | COMPLETED |

    # Verify that process notes appear properly
    When I click on ViewNotes button for *Pr_1 in #pmse_InboxList.ListView
    Then I verify the last note in BPM pop-up window
      | note                         |
      | Administrator <rejectedNote> |
    # Delete Process Notes
    When I delete last note in BPM pop-up window
    When I close BPM pop-up window

    Examples:
      | approvedNote                           | rejectedNote                               |
      | Approved! Thank you for the good work! | Rejected! Please spend more time fixing it |
