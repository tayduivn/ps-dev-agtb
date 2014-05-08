/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('HistoricalSummary', ['view'], {
            /**
             * @inheritdoc
             *
             * Bind the historical summary button handler.
             */
            onAttach: function(component, plugin) {
                this.on('init', function() {
                    this.context.on('button:historical_summary_button:click', this.historicalSummaryClicked, this);
                });
            },

            /**
             * Handles the click event, and open the historical-summary-list view
             */
            historicalSummaryClicked: function() {
                var context = this.context.getChildContext({
                    module: 'History'
                });
                app.drawer.open({
                    layout: 'history-summary',
                    context: context
                });
            },

            /**
             * @inheritdoc
             *
             * Clean up associated event handlers.
             */
            onDetach: function(component, plugin) {
                this.context.off('button:historical_summary_button:click', this.auditClicked, this);
            }
        });
    });
})(SUGAR.App);
