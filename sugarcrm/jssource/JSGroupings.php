<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2005 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*
 * This is the array that is used to determine how to group/concatenate js files together
 * The format is to define the location of the file to be concatenated as the array element key
 * and the location of the file to be created that holds the child files as the array element value.
 * So: $original_file_location => $Concatenated_file_location
 *
 * If you wish to add a grouping that contains a file that is part of another group already,
 * add a '.' after the .js in order to make the element key unique.  Make sure you pare the extension out
 *
 */
        if(!function_exists('getSubgroupForTarget'))
        {
            /**
             * Helper to allow for getting sub groups of combinations of includes that are likely to be required by
             * many clients (so that we don't end up with duplication from client to client).
             * @param  string $subGroup The sub-group
             * @param  string $target The target file to point to e.g. '<app>/<app>.min.js',
             * @return array array of key vals where the keys are source files and values are the $target passed in.
             */
            function getSubgroupForTarget ($subGroup, $target) {
                // Add more sub-groups as needed here if client include duplication in $js_groupings
                switch ($subGroup) {
                    case 'bootstrap':
                        return array(
                            'styleguide/assets/js/bootstrap-button.js'  => $target,
                            'styleguide/assets/js/bootstrap-tooltip.js' => $target,
                            'styleguide/assets/js/bootstrap-dropdown.js'=>  $target,
                            'styleguide/assets/js/bootstrap-popover.js' => $target,
                            'styleguide/assets/js/bootstrap-modal.js'   => $target,
                            'styleguide/assets/js/bootstrap-alert.js'   => $target,
                            'styleguide/assets/js/bootstrap-datepicker.js' => $target,
                        );
                        break;
                    case 'bootstrap_core':
                        return array(
                            'include/javascript/jquery/bootstrap/bootstrap.min.js'       =>   $target,
                            'include/javascript/jquery/jquery.popoverext.js'             =>   $target,
                        );
                        break;
                    // these are the only files not in bootstrap.min.js that we need in forecasts
                    // as bootstrap.min.js is already included in sugar_grp1_bootstrap.js
                    case 'bootstrap_forecasts':
                        return array(
                            'styleguide/assets/js/bootstrap-datepicker.js' => $target,
                        );
                        break;
                    case 'jquery_core':
                        return array (
                            'include/javascript/jquery/jquery-min.js'             =>    $target,
                            'include/javascript/jquery/jquery-ui-min.js'          =>    $target,
                            'include/javascript/jquery/jquery.json-2.3.js'        =>    $target,
                        );
                        break;
                    case 'jquery_menus':
                        return array(
                            'include/javascript/jquery/jquery.hoverIntent.js'            =>   $target,
                            'include/javascript/jquery/jquery.hoverscroll.js'            =>   $target,
                            'include/javascript/jquery/jquery.hotkeys.js'                =>   $target,
                            'include/javascript/jquery/jquery.tipTip.js'              	 =>   $target,
                            'include/javascript/jquery/jquery.sugarMenu.js'              =>   $target,
                            'include/javascript/jquery/jquery.highLight.js'              =>   $target,
                            'include/javascript/jquery/jquery.showLoading.js'            =>   $target,
                            'include/javascript/jquery/jquery.jstree.js'              	 =>   $target,
                            'include/javascript/jquery/jquery.dataTables.min.js'         =>   $target,
                            'include/javascript/jquery/jquery.dataTables.customSort.js'  =>   $target,
                            'include/javascript/jquery/jquery.jeditable.js'              =>   $target,
                            'include/javascript/jquery/jquery.effects.custombounce.js'   =>   $target,
                        );
                        break;
                    default:
                        break;
                }
            }
        }

        $js_groupings = array(
            $summer_js = array(
                "sidecar/lib/jquery/jquery.min.js" => "summer/summer.min.js",
                "sidecar/lib/jquery/jquery.iframe.transport.js" => "summer/summer.min.js",
                "sidecar/lib/jquery-ui/js/jquery-ui-1.8.18.custom.min.js" => "summer/summer.min.js",
                "sidecar/lib/backbone/underscore.js" => "summer/summer.min.js",
                "sidecar/lib/backbone/backbone.js" => "summer/summer.min.js",
                "sidecar/lib/handlebars/handlebars-1.0.rc.1.js" => "summer/summer.min.js",
                "sidecar/lib/stash/stash.js" => "summer/summer.min.js",
                "sidecar/lib/async/async.js" => "summer/summer.min.js",
                "sidecar/lib/sugar/sugar.searchahead.js" => "summer/summer.min.js",
                "sidecar/lib/sugar/sugar.timeago.js" => "summer/summer.min.js",
                "summer/lib/TimelineJS/js/storyjs-embed.js" => "summer/summer.min.js",
                "summer/lib/fullcalendar/fullcalendar.js" => "summer/summer.min.js",
                "sidecar/lib/sugarapi/sugarapi.js" => "summer/summer.min.js",
                "sidecar/src/app.js" => "summer/summer.min.js",
                "sidecar/src/utils/date.js" => "summer/summer.min.js",
                "sidecar/src/utils/utils.js" => "summer/summer.min.js",
                "sidecar/src/utils/math.js" => "summer/summer.min.js",
                "sidecar/src/utils/currency.js" => "summer/summer.min.js",
                "sidecar/src/core/cache.js" => "summer/summer.min.js",
                "sidecar/src/core/events.js" => "summer/summer.min.js",
                "sidecar/src/core/error.js" => "summer/summer.min.js",
                "summer/error.js" => "summer/summer.min.js",
                "summer/sugarAuthStore.js" => "summer/summer.min.js",
                "sidecar/src/view/template.js" => "summer/summer.min.js",
                "sidecar/src/core/context.js" => "summer/summer.min.js",
                "sidecar/src/core/controller.js" => "summer/summer.min.js",
                "sidecar/src/core/router.js" => "summer/summer.min.js",
                "sidecar/src/core/language.js" => "summer/summer.min.js",
                "sidecar/src/core/metadata-manager.js" => "summer/summer.min.js",
                "sidecar/src/core/acl.js" => "summer/summer.min.js",
                "sidecar/src/core/user.js" => "summer/summer.min.js",
                "summer/user.js" => "summer/summer.min.js",
                "summer/analytics.js" => "summer/summer.min.js",
                "sidecar/src/utils/logger.js" => "summer/summer.min.js",
                "summer/config.js" => "summer/summer.min.js",
                "sidecar/src/data/bean.js" => "summer/summer.min.js",
                "sidecar/src/data/bean-collection.js" => "summer/summer.min.js",
                "sidecar/src/data/data-manager.js" => "summer/summer.min.js",
                "sidecar/src/data/validation.js" => "summer/summer.min.js",
                "sidecar/src/view/hbt-helpers.js" => "summer/summer.min.js",
                "sidecar/src/view/view-manager.js" => "summer/summer.min.js",
                "sidecar/src/view/component.js" => "summer/summer.min.js",
                "sidecar/src/view/view.js" => "summer/summer.min.js",
                "sidecar/src/view/field.js" => "summer/summer.min.js",
                "sidecar/src/view/layout.js" => "summer/summer.min.js",
                "sidecar/src/view/alert.js" => "summer/summer.min.js",
                "summer/summer.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-transition.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-collapse.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-scrollspy.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-tab.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-typeahead.js" => "summer/summer.min.js",
                "summer/lib/twitterbootstrap/js/jquery.dataTables.js" => "summer/summer.min.js",
                "summer/lib/twitterbootstrap/js/wicked.js" => "summer/summer.min.js",
                "styleguide/styleguide/js/jquery.jeditable.js" => "summer/summer.min.js",
                "summer/lib/twitterbootstrap/js/editable.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-button.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-tooltip.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-popover.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-dropdown.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-modal.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-alert.js" => "summer/summer.min.js",
                "summer/summer-ui.js" => "summer/summer.min.js",
                "styleguide/assets/js/nvd3/lib/d3.min.js" => "summer/summer.min.js",
                // To add more models to NV D3, run the makefile in styleguide.
                "styleguide/assets/js/nvd3/nv.d3.min.js" => "summer/summer.min.js",
                "modules/Forecasts/clients/base/lib/ForecastsUtils.js" => "summer/summer.min.js",
                "modules/Forecasts/clients/base/lib/BucketGridEnum.js" => "summer/summer.min.js",
                "modules/Forecasts/clients/base/lib/ClickToEdit.js" => "summer/summer.min.js",
                "modules/Forecasts/clients/base/helper/hbt-helpers.js" => "summer/summer.min.js",
                "include/javascript/twitterbootstrap/js/sugarCharts.js" => "summer/summer.min.js",
                "include/javascript/jquery/jquery.jstree.js" => "summer/summer.min.js",
                "include/javascript/phpjs/base64_encode.js" => "summer/summer.min.js",
            ),

            $summer_css = array(
                "summer/lib/fullcalendar/fullcalendar.css" => "summer/summer.min.css",
                "sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css" => "summer/summer.min.css",
                //"styleguide/styleguide/css/nv.d3.css" => "summer/summer.min.css",
                "summer/lib/TimelineJS/css/timeline.css" => "summer/summer.min.css",
            ),

            $summer_splash_js = array(
                "sidecar/lib/jquery/jquery.min.js" => "summer/summer-splash.min.js",
                "summer/splash/login.js" => "summer/summer-splash.min.js",
                "styleguide/assets/js/bootstrap-alert.js" => "summer/summer-splash.min.js",
            ),

            $summer_splash_css = array(
                "summer/lib/twitterbootstrap/css/bootstrap.css" => "summer/summer-splash.min.css",
                "sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css" => "summer/summer-splash.min.css",
                "summer/splash/static/css/splash.css" => "summer/summer-splash.min.css",
            ),
           $sugar_grp1 = array(
                //scripts loaded on first page
                "sidecar/lib/backbone/underscore.js" => "include/javascript/sugar_grp1.js",
                'include/javascript/sugar_3.js'         => 'include/javascript/sugar_grp1.js',
                'include/javascript/ajaxUI.js'          => 'include/javascript/sugar_grp1.js',
                'include/javascript/cookie.js'          => 'include/javascript/sugar_grp1.js',
                'include/javascript/menu.js'            => 'include/javascript/sugar_grp1.js',
                'include/javascript/calendar.js'        => 'include/javascript/sugar_grp1.js',
                'include/javascript/quickCompose.js'    => 'include/javascript/sugar_grp1.js',
                'include/javascript/yui/build/yuiloader/yuiloader-min.js' => 'include/javascript/sugar_grp1.js',
                //HTML decode
                'include/javascript/phpjs/license.js' => 'include/javascript/sugar_grp1.js',
                'include/javascript/phpjs/get_html_translation_table.js' => 'include/javascript/sugar_grp1.js',
                'include/javascript/phpjs/html_entity_decode.js' => 'include/javascript/sugar_grp1.js',
                'include/javascript/phpjs/htmlentities.js' => 'include/javascript/sugar_grp1.js',
				//BEGIN SUGARCRM flav=pro ONLY
	            //Expression Engine
                'include/Expressions/javascript/expressions.js'  => 'include/javascript/sugar_grp1.js',
	            'include/Expressions/javascript/dependency.js'   => 'include/javascript/sugar_grp1.js',
	            //END SUGARCRM flav=pro ONLY
                'include/EditView/Panels.js'   => 'include/javascript/sugar_grp1.js',
            ),
			// solo jquery libraries
			$sugar_grp_jquery_core = getSubgroupForTarget('jquery_core', 'include/javascript/sugar_grp1_jquery_core.js'),

            //bootstrap
            $sugar_grp_bootstrap = getSubgroupForTarget('bootstrap_core', 'include/javascript/sugar_grp1_bootstrap.js'),

            //jquery for moddule menus
            $sugar_grp_jquery_menus = getSubgroupForTarget('jquery_menus', 'include/javascript/sugar_grp1_jquery_menus.js'),

            //core app jquery libraries
			$sugar_grp_jquery = array_merge(getSubgroupForTarget('jquery_core', 'include/javascript/sugar_grp1_jquery.js'),
                getSubgroupForTarget('bootstrap_core', 'include/javascript/sugar_grp1_jquery.js'),
                getSubgroupForTarget('jquery_menus', 'include/javascript/sugar_grp1_jquery.js')
            ),

           $sugar_field_grp = array(
               'include/SugarFields/Fields/Collection/SugarFieldCollection.js' => 'include/javascript/sugar_field_grp.js',
               //BEGIN SUGARCRM flav=pro ONLY
               'include/SugarFields/Fields/Teamset/Teamset.js' => 'include/javascript/sugar_field_grp.js',
               //END SUGARCRM flav=pro ONLY
               'include/SugarFields/Fields/Datetimecombo/Datetimecombo.js' => 'include/javascript/sugar_field_grp.js',
           ),
            $sugar_grp1_yui = array(
			//YUI scripts loaded on first page
            'include/javascript/yui3/build/yui/yui-min.js'              => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui3/build/loader/loader-min.js'        => 'include/javascript/sugar_grp1_yui.js',
			'include/javascript/yui/build/yahoo/yahoo-min.js'           => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/dom/dom-min.js'               => 'include/javascript/sugar_grp1_yui.js',
			'include/javascript/yui/build/yahoo-dom-event/yahoo-dom-event.js'
			    => 'include/javascript/sugar_grp1_yui.js',
			'include/javascript/yui/build/event/event-min.js'           => 'include/javascript/sugar_grp1_yui.js',
			'include/javascript/yui/build/logger/logger-min.js'         => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/animation/animation-min.js'   => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/connection/connection-min.js' => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/dragdrop/dragdrop-min.js'     => 'include/javascript/sugar_grp1_yui.js',
            //Ensure we grad the SLIDETOP custom container animation
            'include/javascript/yui/build/container/container-min.js'   => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/element/element-min.js'       => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/tabview/tabview-min.js'       => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/selector/selector.js'     => 'include/javascript/sugar_grp1_yui.js',
            //This should probably be removed as it is not often used with the rest of YUI
            'include/javascript/yui/ygDDList.js'                        => 'include/javascript/sugar_grp1_yui.js',
            //YUI based quicksearch
            'include/javascript/yui/build/datasource/datasource-min.js' => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/json/json-min.js'             => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/autocomplete/autocomplete-min.js'=> 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/quicksearch.js'                         => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/menu/menu-min.js'             => 'include/javascript/sugar_grp1_yui.js',
			'include/javascript/sugar_connection_event_listener.js'     => 'include/javascript/sugar_grp1_yui.js',
			'include/javascript/yui/build/calendar/calendar.js'     => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/history/history.js'     => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/resize/resize-min.js'     => 'include/javascript/sugar_grp1_yui.js',
            ),

            $sugar_grp_yui_widgets = array(
			//sugar_grp1_yui must be laoded before sugar_grp_yui_widgets
            'include/javascript/yui/build/datatable/datatable-min.js'   => 'include/javascript/sugar_grp_yui_widgets.js',
            'include/javascript/yui/build/treeview/treeview-min.js'     => 'include/javascript/sugar_grp_yui_widgets.js',
			'include/javascript/yui/build/button/button-min.js'         => 'include/javascript/sugar_grp_yui_widgets.js',
            'include/javascript/yui/build/calendar/calendar-min.js'     => 'include/javascript/sugar_grp_yui_widgets.js',
			'include/javascript/sugarwidgets/SugarYUIWidgets.js'        => 'include/javascript/sugar_grp_yui_widgets.js',
            // Include any Sugar overrides done to YUI libs for bugfixes
            'include/javascript/sugar_yui_overrides.js'   => 'include/javascript/sugar_grp_yui_widgets.js',
            ),

			$sugar_grp_yui_widgets_css = array(
				"include/javascript/yui/build/fonts/fonts-min.css" => 'include/javascript/sugar_grp_yui_widgets.css',
				"include/javascript/yui/build/treeview/assets/skins/sam/treeview.css"
					=> 'include/javascript/sugar_grp_yui_widgets.css',
				"include/javascript/yui/build/datatable/assets/skins/sam/datatable.css"
					=> 'include/javascript/sugar_grp_yui_widgets.css',
				"include/javascript/yui/build/container/assets/skins/sam/container.css"
					=> 'include/javascript/sugar_grp_yui_widgets.css',
                "include/javascript/yui/build/button/assets/skins/sam/button.css"
					=> 'include/javascript/sugar_grp_yui_widgets.css',
				"include/javascript/yui/build/calendar/assets/skins/sam/calendar.css"
					=> 'include/javascript/sugar_grp_yui_widgets.css',
			),

            $sugar_grp_yui2 = array(
            //YUI combination 2
            'include/javascript/yui/build/dragdrop/dragdrop-min.js'    => 'include/javascript/sugar_grp_yui2.js',
            'include/javascript/yui/build/container/container-min.js'  => 'include/javascript/sugar_grp_yui2.js',
            ),

            //Grouping for emails module.
            $sugar_grp_emails = array(
            'include/javascript/yui/ygDDList.js' => 'include/javascript/sugar_grp_emails.js',
            'include/SugarEmailAddress/SugarEmailAddress.js' => 'include/javascript/sugar_grp_emails.js',
            'include/SugarFields/Fields/Collection/SugarFieldCollection.js' => 'include/javascript/sugar_grp_emails.js',
            //BEGIN SUGARCRM flav=pro ONLY
            'include/SugarRouting/javascript/SugarRouting.js' => 'include/javascript/sugar_grp_emails.js',
            'include/SugarDependentDropdown/javascript/SugarDependentDropdown.js' => 'include/javascript/sugar_grp_emails.js',
            //END SUGARCRM flav=pro ONLY
            'modules/InboundEmail/InboundEmail.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/EmailUIShared.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/EmailUI.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/EmailUICompose.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/ajax.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/grid.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/init.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/complexLayout.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/composeEmailTemplate.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/displayOneEmailTemplate.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/viewPrintable.js' => 'include/javascript/sugar_grp_emails.js',
            'include/javascript/quicksearch.js' => 'include/javascript/sugar_grp_emails.js',

            ),

            //Grouping for the quick compose functionality.
            $sugar_grp_quick_compose = array(
            'include/javascript/jsclass_base.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'include/javascript/jsclass_async.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'modules/Emails/javascript/vars.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'include/SugarFields/Fields/Collection/SugarFieldCollection.js' => 'include/javascript/sugar_grp_quickcomp.js', //For team selection
            'modules/Emails/javascript/EmailUIShared.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'modules/Emails/javascript/ajax.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'modules/Emails/javascript/grid.js' => 'include/javascript/sugar_grp_quickcomp.js', //For address book
            'modules/Emails/javascript/EmailUICompose.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'modules/Emails/javascript/composeEmailTemplate.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'modules/Emails/javascript/complexLayout.js' => 'include/javascript/sugar_grp_quickcomp.js',
            ),

            $sugar_grp_jsolait = array(
                'include/javascript/jsclass_base.js'    => 'include/javascript/sugar_grp_jsolait.js',
                'include/javascript/jsclass_async.js'   => 'include/javascript/sugar_grp_jsolait.js',
                'modules/Meetings/jsclass_scheduler.js'   => 'include/javascript/sugar_grp_jsolait.js',
            ),
           $sugar_grp_sidecar = array_merge(
                array('include/javascript/phpjs/base64_encode.js' => 'include/javascript/sugar_sidecar.min.js',
               'sidecar/lib/jquery/jquery.placeholder.min.js' => 'include/javascript/sugar_sidecar.min.js'),
                getSubgroupForTarget('bootstrap', 'include/javascript/sugar_sidecar.min.js'),
               array(
                   'styleguide/assets/js/bootstrap-tab.js'   => 'include/javascript/sugar_sidecar.min.js',
                   'styleguide/assets/js/jquery.timepicker.js'=> 'include/javascript/sugar_sidecar.min.js',
                   'include/javascript/select2-release-3.3.2/select2.js' => "include/javascript/sugar_sidecar.min.js",
                   'styleguide/assets/js/bootstrap-collapse.js'   => 'include/javascript/sugar_sidecar.min.js',
                   // D3 library
                   'styleguide/assets/js/nvd3/lib/d3.min.js' => 'include/javascript/sugar_sidecar.min.js',
                   'styleguide/assets/js/nvd3/lib/topojson.js' => 'include/javascript/sugar_sidecar.min.js',
                   // To add more models to NV D3, run the makefile in styleguide/js/nvd3.
                   'styleguide/assets/js/nvd3/nv.d3.min.js' => 'include/javascript/sugar_sidecar.min.js',
                   'portal2/error.js'               => 'include/javascript/sugar_sidecar.min.js',
                   'portal2/views/alert-view.js'    => 'include/javascript/sugar_sidecar.min.js',
                   'include/javascript/jquery/jquery.jstree.js' => 'include/javascript/sugar_sidecar.min.js',
                   'include/javascript/jquery/jquery.popoverext.js'           => 'include/javascript/sugar_sidecar.min.js',
                   'include/javascript/jquery/jquery.effects.custombounce.js'           => 'include/javascript/sugar_sidecar.min.js',
                   'include/javascript/jquery/jquery.nouislider.js' => 'include/javascript/sugar_sidecar.min.js',
                   //BEGIN SUGARCRM flav=pro ONLY
                   //Expression Engine
                   'include/Expressions/javascript/expressions.js'  => 'include/javascript/sugar_sidecar.min.js',
                   'include/Expressions/javascript/sidecarExpressionContext.js'   => 'include/javascript/sugar_sidecar.min.js',
                   //END SUGARCRM flav=pro ONLY

                    // Plugins for Sugar 7.
                    'include/javascript/sugar7/plugins/dragdrop_attachments.js'  => 'include/javascript/sugar_sidecar.min.js',
                    'include/javascript/sugar7/plugins/file_dragoff.js'  => 'include/javascript/sugar_sidecar.min.js',
                    'include/javascript/sugar7/plugins/dropdown.js'  => 'include/javascript/sugar_sidecar.min.js',
                    'include/javascript/sugar7/plugins/ellipsis_inline.js'  => 'include/javascript/sugar_sidecar.min.js',
                    'include/javascript/sugar7/plugins/list-column-ellipsis.js'  => 'include/javascript/sugar_sidecar.min.js',
                    'include/javascript/sugar7/plugins/taggable.js'  => 'include/javascript/sugar_sidecar.min.js',
                    'include/javascript/sugar7/plugins/timeago.js'  => 'include/javascript/sugar_sidecar.min.js',
                    'include/javascript/sugar7/plugins/error-decoration.js'  => 'include/javascript/sugar_sidecar.min.js',
                    'include/javascript/sugar7/plugins/quicksearchfilter.js'  => 'include/javascript/sugar_sidecar.min.js',
                    'include/javascript/sugar7/plugins/GridBuilder.js'  => 'include/javascript/sugar_sidecar.min.js',
                    'include/javascript/sugar7/plugins/list-disable-sort.js'  => 'include/javascript/sugar_sidecar.min.js',

                    // Support Portal features for Sugar7
                    //BEGIN SUGARCRM flav=ent ONLY
                    'modules/Contacts/clients/base/lib/bean.js' => 'include/javascript/sugar_sidecar.min.js',
                    //END SUGARCRM flav=ent ONLY
                )
           ),
           //BEGIN SUGARCRM flav=ent ONLY
            $sugar_grp_portal2 = array_merge(
                array('sidecar/lib/jquery/jquery.placeholder.min.js' => 'portal2/portal.min.js'), // preserve ordering
                array(
                    'portal2/error.js'               => 'portal2/portal.min.js',
                    'portal2/user.js'                => 'portal2/portal.min.js',
                    'portal2/portal.js'              => 'portal2/portal.min.js',
                    'portal2/portal-ui.js'           => 'portal2/portal.min.js',
                    'include/javascript/jquery/jquery.popoverext.js'           => 'portal2/portal.min.js',
                    'include/javascript/jquery/jquery.effects.custombounce.js'           => 'portal2/portal.min.js',
                )
            ),
           //END SUGARCRM flav=ent ONLY
        );

    //BEGIN SUGARCRM flav=pro ONLY
    // groupings for sidecar forecast
    // use sidecar/src/include-manifest.php file to define what files should be loaded
    // exclude lib/jquery/jquery.min.js b/s jquery is loaded and extended with sugar_grp1_jquery.js
    $sidecar_forecasts = array();
    $cached_file = 'include/javascript/sidecar_forecasts.js';

    $sidecar_forecasts = array();
    $sidecar_forecasts['include/javascript/jquery/jquery.dataTables.min.js'] = $cached_file;
    $sidecar_forecasts['include/javascript/jquery/jquery.dataTables.customSort.js'] = $cached_file;
    $sidecar_forecasts['include/javascript/jquery/jquery.jeditable.js'] = $cached_file;
    $sidecar_forecasts['include/javascript/jquery/jquery.jstree.js'] = $cached_file;
    // cookie.js is needed by jit.js, including in case we need to display legacy (i. e. non-NVD3) charts in Forecasts
    $sidecar_forecasts['include/javascript/cookie.js'] = $cached_file;
    $sidecar_forecasts['include/SugarCharts/Jit/js/Jit/jit.js'] = $cached_file;
    $sidecar_forecasts['include/SugarCharts/Jit/js/sugarCharts.js'] = $cached_file;
    $sidecar_forecasts['modules/Forecasts/clients/base/helper/hbt-helpers.js'] = $cached_file;
    $sidecar_forecasts['modules/Forecasts/clients/base/lib/ForecastsUtils.js'] = $cached_file;
    $sidecar_forecasts['modules/Forecasts/clients/base/lib/error.js'] = $cached_file;

    $js_groupings[] = $sidecar_forecasts;
    //END SUGARCRM flav=pro ONLY

    /**
     * Check for custom additions to this code
     */

    if(!class_exists('SugarAutoLoader')) {
        // This block is required because this file could be called from a non-entrypoint (such as jssource/minify.php).
        require_once('include/utils/autoloader.php');
        SugarAutoLoader::init();
    }

    foreach(SugarAutoLoader::existing("custom/jssource/JSGroupings.php", SugarAutoLoader::loadExtension("jsgroupings")) as $file) {
        require $file;
    }

