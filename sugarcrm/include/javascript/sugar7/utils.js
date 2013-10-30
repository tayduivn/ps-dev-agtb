/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
(function(app) {
    app.events.on("app:init", function() {
        app.utils = _.extend(app.utils, {
            tooltip: {
                /**
                 * Initializes tooltips for given elements
                 * @param {jQuery} $elements
                 * @param {object} (optional) options - see bootstrap-tooltip docs
                 * @returns {jQuery}
                 */
                initialize: function($elements, options) {
                    return $elements.tooltip(_.extend({}, {
                        container: 'body',
                        trigger: 'hover' //show tooltip on hover only (not on focus)
                    }, options));
                },

                /**
                 * Destroy tooltips
                 * @param {jQuery} $tooltips
                 */
                destroy: function($tooltips) {
                    if ($tooltips) {
                        _.each($tooltips, function(tooltip) {
                            if (this.has(tooltip)) {
                                $(tooltip).tooltip('destroy');
                            }
                        }, this);
                    }
                },

                /**
                 * Does the given element have tooltip?
                 * @param {DOM} element
                 * @returns {boolean}
                 */
                has: function(element) {
                    return !_.isUndefined($(element).data('tooltip'));
                }
            },

            /**
             * Takes two Forecasts models and returns HTML for the history log
             *
             * @param oldestModel {Backbone.Model} the oldest model by date_entered
             * @param newestModel {Backbone.Model} the most recent model by date_entered
             * @return {Object}
             */
            createHistoryLog: function(oldestModel, newestModel) {
                var is_first_commit = false;

                if(_.isEmpty(oldestModel)) {
                    oldestModel = new Backbone.Model({
                        best_case: 0,
                        likely_case: 0,
                        worst_case: 0,
                        date_entered: ''
                    });
                    is_first_commit = true;
                }
                var best_difference = this.getDifference(oldestModel, newestModel, 'best_case'),
                    best_direction = this.getDirection(best_difference),
                    likely_difference = this.getDifference(oldestModel, newestModel, 'likely_case'),
                    likely_direction = this.getDirection(likely_difference),
                    worst_difference = this.getDifference(oldestModel, newestModel, 'worst_case'),
                    worst_direction = this.getDirection(worst_difference),
                    args = [],
                    best_arrow = this.getArrowDirectionSpan(best_direction),
                    likely_arrow = this.getArrowDirectionSpan(likely_direction),
                    worst_arrow = this.getArrowDirectionSpan(worst_direction),
                    num_shown = 0,
                    hb = Handlebars.compile("{{{str key module args}}}"),
                    lang_string_key = '',
                    final_args = [],
                    labels = [],
                    likely_args = {
                        changed: likely_difference != 0,
                        show: app.metadata.getModule('Forecasts', 'config').show_worksheet_likely
                    },
                    best_args = {
                        changed: best_difference != 0,
                        show: app.metadata.getModule('Forecasts', 'config').show_worksheet_best
                    },
                    worst_args = {
                        changed: worst_difference != 0,
                        show: app.metadata.getModule('Forecasts', 'config').show_worksheet_worst
                    };

                // increment num_shown for each variable that is true
                likely_args.show ? num_shown++ : '';
                best_args.show ? num_shown++ : '';
                worst_args.show ? num_shown++ : '';

                // set the key for the lang string
                lang_string_key = 'LBL_COMMITTED_HISTORY_' + num_shown + '_SHOWN';

                //determine what changed and add parts to the array for displaying the changes
                if(likely_args.changed && likely_args.show) {
                    final_args.push(
                        this.gatherLangArgsByParams(likely_direction, likely_arrow, likely_difference, newestModel, 'likely_case')
                    );
                } else if(likely_args.show) {
                    // push an empty array for args
                    final_args.push([]);
                }

                if(best_args.changed && best_args.show) {
                    final_args.push(
                        this.gatherLangArgsByParams(best_direction, best_arrow, best_difference, newestModel, 'best_case')
                    );
                } else if(best_args.show) {
                    // push an empty array for args
                    final_args.push([]);
                }

                if(worst_args.changed && worst_args.show) {
                    final_args.push(
                        this.gatherLangArgsByParams(worst_direction, worst_arrow, worst_difference, newestModel, 'worst_case')
                    );
                } else if(worst_args.show) {
                    // push an empty array for args
                    final_args.push([]);
                }

                // get the final args to go into the main text
                labels = this.getCommittedHistoryLabel(best_args, likely_args, worst_args, is_first_commit);

                final_args = this.parseArgsAndLabels(final_args, labels);

                //Compile the language string for the log
                var text = hb({'key': lang_string_key, 'module': "Forecasts", 'args': final_args});

                // need to tell Handelbars not to escape the string when it renders it, since there might be
                // html in the string, args returned for testing purposes
                return {'text': new Handlebars.SafeString(text)};
            },

            /**
             * Returns an array of three args for the html for the arrow, the difference (amount changed), and the new value
             *
             * @param dir {String} direction of the arrow, LBL_UP/LBL_DOWN
             * @param arrow {String} HTML for the arrow string
             * @param diff {Number} difference between the new model and old model
             * @param model {Backbone.Model} the newestModel being used so we can get the current caseStr
             * @param attrStr {String} the attr string to get from the newest model
             */
            gatherLangArgsByParams: function(dir, arrow, diff, model, attrStr) {
                return {
                    'direction' : new Handlebars.SafeString(app.lang.get(dir, 'Forecasts') + arrow),
                    'from' :app.currency.formatAmountLocale(Math.abs(diff)),
                    'to' : app.currency.formatAmountLocale(model.get(attrStr))
                };
            },

            /**
             * checks the direction class passed in to determine what span to create to show the appropriate arrow
             * or lack of arrow to display on the
             * @param directionClass class being used for the label ('LBL_UP' or 'LBL_DOWN')
             * @return {String}
             */
            getArrowDirectionSpan: function(directionClass) {
                return directionClass == "LBL_UP" ? '&nbsp;<i class="icon-arrow-up font-green"></i>' :
                    directionClass == "LBL_DOWN" ? '&nbsp;<i class="icon-arrow-down font-red"></i>' : '';
            },

            /**
             * Returns the CSS classes for an up or down arrow icon
             *
             * @param newValue the new value
             * @param oldValue the previous value
             * @return {String} css classes for up or down arrow icons, if the values didn't change, returns ''
             */
            getArrowIconColorClass: function(newValue, oldValue) {
                var diff = Math.abs(newValue - oldValue),
                    cls = '';
                // due to decimal rounding on the front end, we only want to know about differences greater
                // of two decimal places.
                // todo-sfa: This hardcoded 0.01 value needs to be changed to a value determined by userprefs
                if(diff >= 0.01) {
                    cls = (newValue > oldValue) ? ' icon-arrow-up font-green' : ' icon-arrow-down font-red';
                }
                return cls;
            },

            /**
             * Centralizes our forecast type switch.
             *
             * @param isManager
             * @param showOpps
             * @return {String} 'Direct' or 'Rollup'
             */
            getForecastType: function(isManager, showOpps) {
                /**
                 * Three cases exist when a row is showing commitLog icon:
                 *
                 * Manager  - showOpps=1 - isManager=1 => Manager's Opportunities row - forecast_type = 'Direct'
                 * Manager  - showOpps=0 - isManager=1 => Manager has another manager in their ManagerWorksheet - forecast_type = 'Rollup'
                 * Rep      - showOpps=0 - isManager=0 => Sales Rep (not a manager) row - forecast_type = 'Direct'
                 *
                 */
                return (!showOpps && isManager) ? 'Rollup' : 'Direct';
            },

            /**
             * builds the args to look up for the history label based on what has changed in the model
             * @param best {Object}
             * @param likely {Object}
             * @param worst {Object}
             * @param is_first_commit {bool}
             * @return {Array}
             */
            getCommittedHistoryLabel: function(best, likely, worst, is_first_commit) {
                var args = [];

                // Handle if this is the first commit
                if(is_first_commit) {
                    args.push('LBL_COMMITTED_HISTORY_SETUP_FORECAST');
                } else {
                    args.push('LBL_COMMITTED_HISTORY_UPDATED_FORECAST');
                }

                // Handle Likely
                if(likely.show) {
                    if(likely.changed) {
                        args.push('LBL_COMMITTED_HISTORY_LIKELY_CHANGED');
                    } else {
                        args.push('LBL_COMMITTED_HISTORY_LIKELY_SAME');
                    }
                }

                // Handle Best
                if(best.show) {
                    if(best.changed) {
                        args.push('LBL_COMMITTED_HISTORY_BEST_CHANGED');
                    } else {
                        args.push('LBL_COMMITTED_HISTORY_BEST_SAME');
                    }
                }

                // Handle Worst
                if(worst.show) {
                    if(worst.changed) {
                        args.push('LBL_COMMITTED_HISTORY_WORST_CHANGED');
                    } else {
                        args.push('LBL_COMMITTED_HISTORY_WORST_SAME');
                    }
                }

                return args;
            },

            /**
             * Parses through labels array and adds the proper args in to the string
             *
             * @param argsArray {Array} of args (direction arrow html, amount difference and the new amount)
             * @param labels {Array} of lang key labels to use
             * @return {Array}
             */
            parseArgsAndLabels: function(argsArray, labels) {
                var retArgs = {},
                    argsKeys = ['first', 'second', 'third'],
                    hb = Handlebars.compile("{{{str key module args}}}");

                // labels should have one more item in its array than argsArray
                // because of the SETUP or UPDATED label which has no args
                if((argsArray.length + 1) != labels.length) {
                    // SOMETHING CRAAAAZY HAPPENED!
                    app.logger.error('ForecastsUtils.parseArgsAndLabels() :: argsArray and labels params are not the same length ');
                    return null;
                }

                // get the first argument off the label array
                retArgs.intro = hb({'key': _.first(labels), 'module': 'Forecasts', 'args': []});

                // get the other values, with out the first value
                labels = _.last(labels, labels.length - 1);

                // loop though all the other values
                _.each(labels, function(label, index) {
                    retArgs[argsKeys[index]] = hb({'key': label, 'module': 'Forecasts', 'args': argsArray[index]});
                });

                return retArgs;
            },

            /**
             * Returns the difference between the newest model and the oldest
             *
             * @param oldModel {Backbone.Model}
             * @param newModel {Backbone.Model}
             * @param attr {String} the attribute key to get from the models
             * @return {*}
             */
            getDifference: function(oldModel, newModel, attr) {
                var diff = newModel.get(attr) - oldModel.get(attr);
                /**
                 * if the difference is between -0.01 and 0.01 not including those numbers,
                 * set the diff to zero otherwise you get "Forecast went up $0.00 to..." when the difference is < 0.01
                 * because it gets rounded later
                 */
                //
                return (Math.abs(diff) < 0.01) ? 0 : diff;
            },


            /**
             * Returns the proper direction label to use
             *
             * @param difference the amount of difference between newest and oldest models
             * @return {String} LBL_UP, LBL_DOWN, or ''
             */
            getDirection: function(difference) {
                return difference > 0 ? 'LBL_UP' : (difference < 0 ? 'LBL_DOWN' : '');
            },

            /**
             * Returns the subpanel list with link module name and corresponding LBL_
             *
             * @param module
             * @return {Object} The subpanel list
             */
            getSubpanelList: function(module) {
                var list = {},
                    subpanels = app.metadata.getModule(module).layouts.subpanels;
                if (subpanels && subpanels.meta && subpanels.meta.components) {
                    _.each(subpanels.meta.components, function(comp) {
                        if (comp.context && comp.context.link) {
                            list[comp.label] = comp.context.link;
                        } else {
                            app.logger.warning("Subpanel's subpanels.meta.components has component with no context or context.link");
                        }
                    });
                }
                return list;
            },

            /**
             * Returns TRUE if any of the related fields associated with this link are required,
             * which would make this link required.  Returns FALSE otherwise.
             *
             * @param {String} module Parent module name
             * @param {String} link Link name
             * @return {Boolean}
             */
            isRequiredLink: function(module, link){
                var relatedFields = app.data.getRelateFields(module, link),
                    requiredField = _.some(relatedFields, function(field){
                        return field.required === true;
                    }, this);
                return requiredField;
            },

            /**
             * Contains a list of column names from metadata and maps them to correct config param
             * e.g. 'likely_case' column is controlled by the Forecast config.show_worksheet_likely param
             * Used by forecastsWorksheetManager, forecastsWorksheetManagerTotals
             *
             * @property tableColumnsConfigKeyMapManager
             * @private
             */
            _tableColumnsConfigKeyMapManager: {
                'likely_case': 'show_worksheet_likely',
                'likely_case_adjusted': 'show_worksheet_likely',
                'best_case': 'show_worksheet_best',
                'best_case_adjusted': 'show_worksheet_best',
                'worst_case': 'show_worksheet_worst',
                'worst_case_adjusted': 'show_worksheet_worst'
            },

            /**
             * Contains a list of column names from metadata and maps them to correct config param
             * e.g. 'likely_case' column is controlled by the Forecast config.show_worksheet_likely param
             * Used by forecastsWorksheet, forecastsWorksheetTotals
             *
             * @property tableColumnsConfigKeyMapRep
             * @private
             */
            _tableColumnsConfigKeyMapRep: {
                'likely_case': 'show_worksheet_likely',
                'best_case': 'show_worksheet_best',
                'worst_case': 'show_worksheet_worst'
            },

            /**
             * Function checks the proper _tableColumnsConfigKeyMap___ for the key and returns the config setting
             *
             * @param key {String} table key name (eg: 'likely_case')
             * @param viewName {String} the name of the view calling the function (eg: 'forecastsWorksheet')
             * @return {*}
             */
            getColumnVisFromKeyMap: function(key, viewName) {
                var moduleMap = {
                    'forecastsWorksheet': 'rep',
                    'forecastsWorksheetTotals': 'rep',
                    'forecastsWorksheetManager': 'mgr',
                    'forecastsWorksheetManagerTotals': 'mgr'
                };

                // which key map to use from the moduleMap
                var whichKeyMap = moduleMap[viewName];

                // get the proper keymap
                var keyMap = (whichKeyMap === 'rep') ? this._tableColumnsConfigKeyMapRep : this._tableColumnsConfigKeyMapManager;

                var returnValue = app.metadata.getModule('Forecasts', 'config')[keyMap[key]];
                // If we've been passed a value that doesn't exist in the keymaps
                if(!_.isUndefined(returnValue)) {
                    // convert it to boolean
                    returnValue = returnValue == 1
                } else {
                    // if return value was null (not found) then set to true
                    returnValue = true;
                }
                return returnValue;
            },

            /**
             * If the passed in User is a Manager, then get his direct reportees, and then set the user
             * on the context, if they are not a Manager, just set user to the context
             * @param selectedUser
             * @param context
             */
            getSelectedUsersReportees: function(selectedUser, context) {

                if(selectedUser.is_manager) {
                    // make sure the reportee's array is there
                    if(_.isUndefined(selectedUser.reportees)) {
                        selectedUser.reportees = [];
                    }
                    var url = app.api.buildURL('Users', 'filter'),
                        post_args = {
                            "filter": [
                                {"reports_to_id": selectedUser.id}
                            ],
                            "fields": "full_name"
                        },
                        options = {};
                    options.success = _.bind(function(resp, status, xhr) {
                        _.each(resp.records, function(user) {
                            selectedUser.reportees.push({id: user.id, name: user.full_name});
                        });
                        this.set("selectedUser", selectedUser)
                    }, context);
                    app.api.call("create", url, post_args, options);
                } else {
                    // update context with selected user which will trigger checkRender
                    context.set("selectedUser", selectedUser);
                }
            },

            /**
             * Makes sure that Sales Stage Won/Lost values from the database Forecasts config settings
             * exist in the sales_stage_dom
             *
             * @returns {Boolean} if forecasts is configured to run ok
             */
            checkForecastConfig: function() {
                var forecastConfigOK = true,
                    cfg = app.metadata.getModule('Forecasts', 'config'),
                    salesWonVals = cfg.sales_stage_won,
                    salesLostVals = cfg.sales_stage_lost,
                    salesWonLostVals = cfg.sales_stage_won.concat(cfg.sales_stage_lost),
                    domVals = app.lang.getAppListStrings('sales_stage_dom');

                if(salesWonVals.length == 0 || salesLostVals.length == 0 || _.isEmpty(domVals)) {
                    forecastConfigOK = false;
                } else {
                    forecastConfigOK = _.every(salesWonLostVals, function(val) {
                        return (val != '' && _.has(domVals, val));
                    }, this);
                }

                return forecastConfigOK;
            },

            /**
             * Returns if the current browser has touch events
             *
             * @returns {Boolean}
             */
            isTouchDevice: function() {
                return Modernizr.touch;
            },

            /**
             * Builds a route for module in either bwc or new sidecar.
             *
             * This overrides the normal router to check first if the module
             * is in BWC or not. If not, this will fallback to default
             * {@link Core.Routing#buildRoute}.
             *
             * {@inheritDoc}
             * @param {Boolean} inBwc If `true` it will force bwc, if `false`
             * it will force sidecar, if not defined, will use metadata
             * information on module. This is a temporary param (hack) and will
             * be removed after we change all the views/layouts to be the ones
             * pointing if it should be loaded in BWC or not.
             *
             * @override Core.Routing#buildRoute
             * @see Bwc#buildRoute()
             */
            customBuildRoute: function(moduleOrContext, id, action, inBwc) {
                var module, moduleMeta;

                // Since _.isString(undefined) returns false,
                // the following block prevent going getter block
                if (_.isEmpty(moduleOrContext)) {
                    return '';
                }

                if (_.isString(moduleOrContext)) {
                    module = moduleOrContext;
                } else {
                    module = moduleOrContext.get('module');
                }

                if (_.isEmpty(module) || !app.bwc) {
                    return '';
                }

                moduleMeta = app.metadata.getModule(module) || {};
                if (inBwc === false || (_.isUndefined(inBwc) && !moduleMeta.isBwcEnabled)) {
                    return '';
                }

                return app.bwc.buildRoute(module, id, app.bwc.getAction(action));
            },
            
            /**
             * Add bwcFrame=1 to the URL if it's not there
             * @param {String} url
             * @return String 
             */
            addIframeMark: function(url) {
            	var parts = url.split("?");
            	if(parts[1] && parts[1].indexOf('bwcFrame=1') != -1) return url;
            	return parts[0] + "?" + (parts[1]?parts[1]+"&bwcFrame=1":"bwcFrame=1"); 
            },
            
            /**
             * Remove bwcFrame=1 from the URL if it's not there
             * @param {String} url
             * @return String 
             */
            rmIframeMark: function(url) {
            	var parts = url.split("?");
            	if(!parts[1]) {
            		return url;
            	}
            	// scan and drop bwcFrame=1
            	return parts[0]+"?"+_.reduce(parts[1].split("&"), function(acc, item) {
            		if(item == 'bwcFrame=1') {
            			return acc;
            		} else {
            			return acc?acc+"&"+item:item;
            		}
            	}, '');
            },

            /**
             * Returns a collection of subpanel models from the LHS context
             * Only tested in Record View!
             *
             * @param ctx the LHS context
             * @param {String} module the name of the module to look for
             * @returns {*} returns the collection or undefined
             */
            getSubpanelCollection: function(ctx, module) {
                var retCollection = undefined,
                    mdl = _.find(ctx.children, function(child) {
                        return (child.get('module') == module);
                    });
                if(mdl && _.has(mdl.attributes, 'collection')) {
                    retCollection = mdl.get('collection');
                }

                return retCollection;
            },
            /**
             * Extracts the full record name from a model.
             *
             * @param {Bean} the model concerned.
             * @return {String} the record name.
             */
            getRecordName: function(model) {
                // Special case for `Documents`
                if (model.module === 'Documents' && model.has('document_name')) {
                    return model.get('document_name');

                // Special case for `Person` type modules
                } else if (model.has('full_name')) {
                    return model.get('full_name');

                // Special case for `Person` type modules
                } else if (model.has('first_name') && model.has('last_name')) {
                    return model.get('first_name') + ' ' + model.get('last_name');

                // Default behavior
                } else {
                    return model.get('name') || '';
                }
            }
        });
    });
})(SUGAR.App);
