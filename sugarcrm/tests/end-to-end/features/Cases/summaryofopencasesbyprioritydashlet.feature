# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.
@ci-excluded
# Temporarily disable this test until new Dashboard behavior is complete
Feature: Summary Of Open Cases by Priority Verification.
  As a Sugar user, I would like to monitor the open cases by Priority.

  Background:
    Given I am logged in

  @e2e @job2
  Scenario: Cases > Verify the number of open cases in the Summary of Open Cases by Priority Dashlet.
    Given Cases records exist:
      | *name | priority | status        |
      | Case0 | P1       | Assigned      |
      | Case1 | P2       | New           |
      | Case2 | P3       | Closed        |
      | Case3 | P1       | Pending Input |
      | Case4 | P2       | Rejected      |
      | Case5 | P3       | Duplicate     |

    When I choose Cases in modules menu
    When I create new dashboard
      | *           | name               |
      | DashboardID | Products Dashboard |

    # Add Product Catalog dashlet to the dashboard
    When I add SavedReportsChart dashlet to #Dashboard
      | label      | saved_report                      |
      | My dashlet | Summary of Open Cases by Priority |

    # Then I verify that dashlet element from #Dashboard.DashboardView still looks like opencasesdashboard
