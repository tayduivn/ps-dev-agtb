# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@admin @job2
Feature: Admin

  Background:
    Given I use default account
    Given I launch App

  @change_system_settings
  Scenario: Admin > Change System Settings
    When I open about view and login
    When I go to "bwc/index.php?module=Administration" url
    When I click on SystemSettings link in #AdminPanel
    # Toggle ON the developer mode checkbox
    When I toggle developerMode checkbox on #AdminPanel:SystemSettings
    When I click on Save button on #AdminPanel:SystemSettings

    When I click on SystemSettings link in #AdminPanel
    # Toggle ON the sugar catalog checkbox
    When I toggle catalog_enabled checkbox on #AdminPanel:SystemSettings
    When I click on Save button on #AdminPanel:SystemSettings

    When I click on SystemSettings link in #AdminPanel
    # Toggle OFF the sugar catalog checkbox
    When I toggle catalog_enabled checkbox on #AdminPanel:SystemSettings
    When I click on Save button on #AdminPanel:SystemSettings

    When I click on SystemSettings link in #AdminPanel
    # Toggle OFF the developer mode checkbox
    When I toggle developerMode checkbox on #AdminPanel:SystemSettings
    When I click on Save button on #AdminPanel:SystemSettings
