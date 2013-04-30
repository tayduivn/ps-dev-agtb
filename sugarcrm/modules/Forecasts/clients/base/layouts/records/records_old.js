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
({
    /**
     * Layout that places views in columns with each view in a column
     * @class View.Layouts.ForecastsLayout
     * @alias SUGAR.App.layout.ForecastsLayout
     * @extends View.Layout
     */

    /**
     * Stores the initial data models
     * todo: use this to populate models that we already have data for; currently only holds filters, chartoptions, & user
     *
     */
    initDataModel: {},

    /**
     * this is used to defer the render until the forecasts initialization returns with the data
     */
    deferredRender: '',

    /**
     * This is used to hold the path for the forecasts specific JS
     */
    forecastsJavascript: "",

    initialize: function(options) {
        var url = app.api.buildURL("Forecasts/init");

        // we need this to be set here so anytime this gets initialized, it will work.
        this.deferredRender = new $.Deferred();
        app.api.call('GET', url, null, {
            success: _.bind(function(data) {

                // Add Forecasts-specific stuff to the app.user object
                app.user.set(data.initData.userData);

                if(data.initData.forecasts_setup === 0) {
                    window.location.hash = "#Forecasts/layout/config";
                } else {
                    this.initForecastsModule(data, options);
                }
            }, this)
        });
    },

    // overload the loadData method as we don't need anything to load here.
    loadData : function() {
        // do nothing here
    },

    initForecastsModule: function(forecastData, options) {
        // set the forecasts specific JS.
        $("#content").append($('<script src="' + forecastData.forecastsJavascript + '"></script>'));

        // get default selections for filter and range
        app.defaultSelections = forecastData.defaultSelections;
        app.initData = forecastData.initData;

        var defaultSelections = app.defaultSelections;

        // Set initial selected data on the context
        options.context.set({
            selectedTimePeriod: defaultSelections.timeperiod_id,
            selectedCategory: defaultSelections.ranges,
            selectedGroupBy: defaultSelections.group_by,
            selectedDataSet: defaultSelections.dataset,

            currentForecastCommitDate: '',

            /**
             * Initially set to the currently logged-in user, selectedUser is different from currentUser
             * because selectedUser is used by other components and is changeable by most components
             * (e.g. selecting a different user via the hierarchy tree or clicking in the worksheet)
             */
            selectedUser: app.user.attributes,

            /**
             * boolean to reload the active worksheet
             */
            reloadWorksheetFlag: false,

            /**
             * The active worksheet
             */
            currentWorksheet: "",

            /**
             * used across Forecasts to contain sales rep worksheet totals
             */
            updatedTotals: {},

            /**
             * todo-sfa keep track of changes to modal.js and when they have proper events being passed
             * we can do away with this
             *
             * set by forecastsConfigTabbedButtons.js when the saved button is clicked so that it's callback
             * can check this variable to know which button was clicked
             */
            saveClicked: false
        });

        // grab a copy of the init data for forecasts to use
        this.initDataModel = app.initData;

        // then get rid of the data from app
        app.initData = null;

        app.view.Layout.prototype.initialize.call(this, options);

        this.deferredRender.resolve();
    },

    /**
     * Add a view (or layout) to this layout.
     * @param {View.Layout/View.View} comp Component to add
     */
    _placeComponent: function(comp) {
        var compName = comp.name || comp.meta.name,
            divName = ".view-" + compName;

        // Certain views in forecasts are controlled by other views
        // If there is a sub-view (eg: a view creates another view and manually renders it in)
        // then we can set placeInLayout => false and we create all the models and such
        // from the rest of metadata, but we just dont place it into the html of the layout
        // as another view will be handling that
        if(_.has(comp, 'meta') && !_.isUndefined(comp.meta) &&
            _.has(comp.meta, 'placeInLayout') && comp.meta.placeInLayout == false) {
            return;
        }

        if(!this.$el.children()[0]) {
            this.$el.addClass("complex-layout");
        }

        //add the components to the div
        if(compName && this.$el.find(divName)[0]) {
            this.$el.find(divName).append(comp.$el);
        } else {
            this.$el.append(comp.$el);
        }
    },

    /**
     * Override render so we can init the alerts for the page to use.
     *
     * @return {*}
     * @private
     */
    _render: function() {
        $.when(this.deferredRender).done(_.bind(function() {
            app.view.Layout.prototype._render.call(this);
            return this;
        }, this));
    }

})
